import os
import uuid
import subprocess
import wave        
import numpy as np 
import torch 

# 1. Suppress warnings for a cleaner server startup log
os.environ["HF_HUB_DISABLE_SYMLINKS_WARNING"] = "1"

from flask import Flask, request, jsonify, send_from_directory
from flask_cors import CORS
from transformers import pipeline, logging, WhisperProcessor, WhisperForConditionalGeneration
from PIL import Image
import io

# 2. Block verbose architecture warnings from Hugging Face
logging.set_verbosity_error()

app = Flask(__name__)
CORS(app)  # Enable CORS for React Frontend communication

# ==========================================
# FILE STORAGE SETUP (NEW)
# ==========================================
# Create a permanent folder to store audio files for the officer dashboard
UPLOAD_FOLDER = os.path.join(os.getcwd(), 'uploads', 'audio')
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# ==========================================
# AI MODEL INITIALIZATION
# ==========================================
print("Initializing County AI Backend...")

print("[1/3] Loading Text Classification Model (BERT)...")
classifier = pipeline("zero-shot-classification", model="facebook/bart-large-mnli")

print("[2/3] Loading Vision Model (BLIP)...")
image_captioner = pipeline("image-text-to-text", model="Salesforce/blip-image-captioning-base")

print("[3/3] Loading Audio Model (Whisper English-Only)...")
whisper_processor = WhisperProcessor.from_pretrained("openai/whisper-small.en")
whisper_model = WhisperForConditionalGeneration.from_pretrained("openai/whisper-small.en")

print("✅ All AI Models Loaded Successfully! Server is listening on Port 5000.")
print("=" * 60)

# ==========================================
# ACTION-BASED MAPPINGS (Context for AI)
# ==========================================
DEPARTMENT_MAPPING = {
    "issues with water supply, dry boreholes, pipes, irrigation, water rationing, burst pipes, dirty tap water, broken pumping station, dry shallow wells, water bowser delays, mkokoteni water vendors extortion, empty water pans, silted dams, blocked irrigation canals, broken drip kits, livestock water crisis, high water bills, WASREB complaints, disconnected piped water, untreated river water, plumbing leaks, erratic low pressure": {"name": "Water & Irrigation", "id": 1},
    
    "issues with bad roads, potholes, transport, public works, impassable mud roads, washed away bridges, broken culverts, dusty roads, murram road grading, peeling tarmac, blocked road drainage, bodaboda stage repair, matatu rank, feeder roads, collapsed bridges, missing speed bumps, traffic jams, heavy machinery needed, street paving, graveling": {"name": "Roads, Transport & Public Works", "id": 2},
    
    "issues with hospitals, clinics, lacking medicine, health services, disease outbreaks, chicken pox, malaria, cholera, typhoid, maternal health, maternity wing, no doctors, striking nurses, delayed ambulances, expired drugs, vaccination drives, polio, measles, skin rash, infectious diseases, public health, dispensaries, broken x-ray machines, full mortuaries, jiggers, SHIF issues": {"name": "Health Services", "id": 3},
    
    "issues with forests, rivers, mining, environment, illegal logging, charcoal burning, deforestation, sand harvesting, quarrying, river pollution, noise pollution, air pollution, climate change, tree planting, encroachment, wetland destruction, toxic spills, poaching, soil erosion, human-wildlife conflict, NEMA complaints, greening initiatives": {"name": "Environment & Natural Resources", "id": 4},
    
    "issues with uncollected garbage, dumping sites, sewage, sanitation, burst sewer lines, overflowing manholes, raw sewage spills, smelly dumpsites, missing garbage trucks, illegal littering, dirty public toilets, pest control, fumigation, rats, flies, drainage unclogging, solid waste disposal, kanjo cleaning delays, deposition of toxic waste, hazardous waste, recycling": {"name": "Sanitation & Waste Management", "id": 5},
    
    "issues with farming, dying crops, livestock, fishing, armyworms, locusts, fertilizer shortages, fake seeds, foot and mouth disease, cattle rustling, tick control, cattle dips, extension officers, fish ponds, fishing nets, drought stress, tractor hire, poultry diseases, cow vaccination, veterinary services, slaughterhouses, shamba pests": {"name": "Agriculture, Livestock & Fisheries", "id": 6},
    
    "issues with markets, businesses, trade, tourism, hawkers, mamamboga stalls, broken market sheds, business permits, exorbitant license fees, kanjo harassment of traders, tourism promotion, industrial parks, SMEs, mitumba stalls, trade fairs, consumer protection, county branding, weights and measures, local investment": {"name": "Trade, Tourism & Industrialization", "id": 7},
    
    "issues with land disputes, housing, physical planning, title deeds, grabbing of public land, boundary disputes, informal settlements, slums, forced evictions, zoning violations, unapproved buildings, squatters, land surveying, land registries, structural integrity, tenancy disputes, urbanization, spatial planning": {"name": "Land, Housing & Physical Planning", "id": 8},
    
    "issues with schools, lacking bursaries, education, uniforms, classrooms, public service, ECD centers, nursery schools, feeding programs, striking teachers, lacking desks, leaking school roofs, scholarships, youth polytechnics, TVET, early childhood development, school infrastructure, CBC classrooms, missing textbooks": {"name": "Education & Public Service", "id": 9},
    
    "issues with the county budget, taxes, revenue, economic planning, corruption, embezzlement, delayed payments, pending bills, over-taxation, cess collection, market tolls, financial allocation, tender disputes, procurement issues, auditing, public participation in budgeting, IFMIS delays": {"name": "Finance & Economic Planning", "id": 10},
    
    "issues with sports, cultural events, games, playground, pitch, jersey, social services, stadium repairs, tournament funding, footballs, boots, youth talent development, local choirs, cultural festivals, heritage sites, community halls, recreational facilities, arts, music, drama, betting addiction support": {"name": "Sports, Culture & Social Services", "id": 11},
    
    "issues concerning gender equality, youth empowerment, people with disabilities, PWDs, wheelchairs, gender-based violence, GBV, women enterprise funds, youth funds, chamas, FGM, child abuse, accessibility ramps, marginalized groups, affirmative action, sign language interpreters, rescue centers, sanitary towels distribution": {"name": "Gender, Youth & PWDs", "id": 12},
    
    "issues with internet, software, computers, e-government services, county websites down, system crashes, no network, cyber security, digital literacy, free public wifi, tech hubs, online portals offline, digital registration, server down, USSD codes failing, automated payment systems, e-citizen county links": {"name": "ICT & E-Government", "id": 13},
    
    "an active fire breakouts, severe floods, building collapse, disaster, emergency, trapped victims, drowning, landslides, mudslides, delayed fire engines, burning houses, rescue operations, evacuation, heavy rains destruction, humanitarian aid, relief food distribution, El Nino damage": {"name": "Disaster Management & Fire Services", "id": 14},
    
    "county administration, governance complaints, staff issues, absent chiefs, bribery, poor service delivery, rude staff, delayed responses, ward administrators, public participation, governor office complaints, village elders, nyumba kumi, sub-county offices, official misconduct": {"name": "County Administration", "id": 15},
    
    "electricity outages, power issues, broken street lighting, blackouts, blown transformers, dark streets, mugging hotspots, mulika mwizi, stolen solar street lights, dangling electrical wires, electrocution risks, KPLC power failures, faulty poles, token meter issues, rural electrification": {"name": "Energy & Street Lighting", "id": 16}
}

AI_DEPT_PHRASES = list(DEPARTMENT_MAPPING.keys())

PRIORITY_MAPPING = {
    "a life-threatening critical emergency, disaster, fatal, immediate danger, trapped, burning, bleeding, collapse, extreme urgency, rescue needed right now": "Critical",
    "a high priority urgent issue needing quick repair, outbreak, severe, major blockage, impassable, rapid response required, hazardous, disease spread": "High",
    "a medium priority routine maintenance issue, broken infrastructure, delayed services, moderate impact, scheduling needed, nuisance, potholes, uncollected trash": "Medium",
    "a low priority general inquiry, minor feedback, suggestions, long-term requests, minor aesthetic issues, general information, policy clarification": "Low"
}

AI_PRIORITY_PHRASES = list(PRIORITY_MAPPING.keys())
# ==========================================
# API ROUTES
# ==========================================

# NEW ROUTE: Serve saved audio files to the frontend dashboard
@app.route('/audio/<filename>', methods=['GET'])
def serve_audio(filename):
    return send_from_directory(UPLOAD_FOLDER, filename)


@app.route('/predict_department', methods=['POST'])
def predict():
    try:
        # 1. Extract Data from Frontend
        complaint_text = request.form.get('text', '').strip()
        image_file = request.files.get('image')
        audio_file = request.files.get('audio')
        
        audio_url = None # Default to None if no audio is uploaded

        # 2. Vision AI Processing
        if image_file:
            print("📸 Processing attached image...")
            image_bytes = image_file.read()
            img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
            
            caption_result = image_captioner(images=img, text="A photograph showing ")
            image_description = caption_result[0]['generated_text']
            print(f"👁️ Vision AI saw: {image_description}")

            if complaint_text:
                complaint_text = f"{complaint_text}. Attached image shows: {image_description}"
            else:
                complaint_text = f"Attached image shows: {image_description}"

        # 3. Audio AI Processing (PERMANENT STORAGE + ENGLISH MODEL)
        if audio_file:
            print("🎙️ Processing attached audio note...")
            
            audio_bytes = audio_file.read()
            file_size = len(audio_bytes)
            print(f"📊 Audio file size received: {file_size} bytes")
            
            if file_size < 1000:
                return jsonify({"error": "Audio recording is too short or empty. Please record again."}), 400

            unique_id = uuid.uuid4().hex
            webm_path = os.path.join(UPLOAD_FOLDER, f"{unique_id}.webm")
            wav_filename = f"{unique_id}.wav"
            wav_path = os.path.join(UPLOAD_FOLDER, wav_filename)
            
            try:
                # Save the raw WebM bytes
                with open(webm_path, 'wb') as f:
                    f.write(audio_bytes)
                
                # Convert to standard 16kHz WAV and save permanently
                print("🔄 Converting audio format...")
                subprocess.run([
                    "ffmpeg", "-i", webm_path, 
                    "-ar", "16000", "-ac", "1", "-c:a", "pcm_s16le", 
                    wav_path, "-y"
                ], check=True, capture_output=True)
                
                # Read the WAV file into a raw math array
                with wave.open(wav_path, "rb") as wf:
                    audio_data = np.frombuffer(wf.readframes(wf.getnframes()), dtype=np.int16)
                    audio_float32 = audio_data.astype(np.float32) / 32768.0
                
                print("🧠 Bypassing Pipeline and talking directly to Whisper AI...")
                
                # Process the raw audio array
                inputs = whisper_processor(audio_float32, sampling_rate=16000, return_tensors="pt")
                
                # Generate transcription directly
                with torch.no_grad():
                    predicted_ids = whisper_model.generate(inputs.input_features)
                
                voice_text = whisper_processor.batch_decode(predicted_ids, skip_special_tokens=True)[0].strip()
                
                print(f"🗣️ AI Heard: {voice_text}")
                
                if complaint_text:
                    complaint_text = f"{complaint_text}. Voice note says: {voice_text}"
                else:
                    complaint_text = f"Voice note says: {voice_text}"
                
                # NEW: Generate the full URL to the saved audio file
                audio_url = f"{request.host_url}audio/{wav_filename}"
                    
            except subprocess.CalledProcessError as e:
                error_log = e.stderr.decode('utf-8')
                print(f"❌ FFmpeg Conversion Error:\n{error_log}")
                return jsonify({"error": "Could not process the audio format. Please try again."}), 500
                
            finally:
                # ONLY delete the temporary WebM file. Keep the WAV file for the dashboard!
                if os.path.exists(webm_path): os.remove(webm_path)

        # 4. Validation Check
        if not complaint_text or len(complaint_text) < 5:
            return jsonify({"error": "Please provide a description, a clear photo, or an audio note."}), 400

        print(f"📝 Final context for NLP: {complaint_text}")

        # 5. Text Classification: Department Prediction
        dept_result = classifier(
            complaint_text, 
            AI_DEPT_PHRASES, 
            hypothesis_template="This citizen complaint is about {}.",
            multi_label=False
        )
        best_dept_phrase = dept_result['labels'][0]
        mapped_dept_data = DEPARTMENT_MAPPING.get(best_dept_phrase)
        
        final_dept_name = mapped_dept_data["name"]
        final_dept_id = mapped_dept_data["id"]
        dept_confidence = dept_result['scores'][0]

        # 6. Text Classification: Priority Prediction
        priority_result = classifier(
            complaint_text, 
            AI_PRIORITY_PHRASES, 
            hypothesis_template="The urgency of this issue is best described as {}.",
            multi_label=False
        )
        best_priority_phrase = priority_result['labels'][0]
        final_priority = PRIORITY_MAPPING.get(best_priority_phrase, "Medium")
        priority_confidence = priority_result['scores'][0]

        # 7. Format Response for React/PHP
        response = {
            "department_name": final_dept_name,
            "department_id": final_dept_id,
            "confidence": round(dept_confidence * 100, 2),
            "priority_level": final_priority,     
            "priority_confidence": round(priority_confidence * 100, 2),
            "processed_text": complaint_text,
            "audio_url": audio_url  # NEW: Pass the URL back to the frontend
        }
        
        print(f"✅ AI Decision: [{final_priority}] -> {final_dept_name} ({response['confidence']}%)")
        if audio_url:
            print(f"🔗 Audio saved at: {audio_url}")
        print("-" * 60)
        return jsonify(response)

    except Exception as e:
        print(f"❌ Error during processing: {e}")
        return jsonify({"error": str(e)}), 500

# ==========================================
# SERVER EXECUTION
# ==========================================
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)