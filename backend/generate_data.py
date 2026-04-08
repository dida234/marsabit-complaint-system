import pandas as pd
import random
from faker import Faker
import time

# 1. SETUP FAKER FOR KENYA
# We use the 'en_KE' locale to generate Kenyan names and phone numbers
fake = Faker('en_KE')

# 2. DEFINE YOUR PROJECT CATEGORIES (Must match your system)
DEPARTMENTS = [
    "Water & Irrigation",
    "Roads, Transport & Public Works",
    "Health Services",
    "Environment & Natural Resources",
    "County Administration"
]

WARDS = [
    "Marsabit Central", "Karare", "Korr", "Loglogo", 
    "Maikona", "Turbi", "North Horr", "Illeret", 
    "Sagante", "Golbo", "Moyale Township", "Sololo"
]

# 3. DEFINE REALISTIC COMPLAINT TEMPLATES
# This ensures the text looks like something a real citizen would write
TEMPLATES = {
    "Water & Irrigation": [
        "The water pipe near the {location} market has burst.",
        "We have not had water in {location} for three days.",
        "The borehole pump in {location} is broken and needs repair.",
        "Illegal water connection spotted near {location} primary school."
    ],
    "Roads, Transport & Public Works": [
        "There is a huge pothole on the road to {location}.",
        "The bridge at {location} river is showing cracks.",
        "Street lights are not working in {location} town center.",
        "The road to {location} is impassable due to heavy rains."
    ],
    "Health Services": [
        "No malaria medicine available at the {location} dispensary.",
        "The doctor at {location} clinic comes very late.",
        "We need an ambulance stationed at {location} urgent care.",
        "Sanitation levels at {location} hospital are very poor."
    ],
    "Environment & Natural Resources": [
        "Garbage is piling up at the {location} stage.",
        "Someone is dumping waste in the river at {location}.",
        "Wild animals are destroying crops near {location}.",
        "Loud noise pollution from the bar in {location}."
    ],
    "County Administration": [
        "How do I apply for the youth fund in {location}?",
        "I need to renew my business permit for my shop in {location}.",
        "When is the governor visiting {location}?",
        "Requesting a meeting with the ward admin of {location}."
    ]
}

# 4. GENERATION LOOP
data = []
print("Generating Synthetic Data...")

for i in range(1, 101): # Generate 100 Records
    # Pick a random department
    dept = random.choice(DEPARTMENTS)
    
    # Pick a random template and fill it with a Ward location
    ward = random.choice(WARDS)
    complaint_text = random.choice(TEMPLATES[dept]).format(location=ward)
    
    # Generate random dates (within last 30 days)
    date_submitted = fake.date_time_between(start_date='-30d', end_date='now')

    # Assign Priority based on keywords (Simulating what your AI does)
    priority = "Normal"
    if any(x in complaint_text.lower() for x in ['burst', 'outbreak', 'accident', 'impassable']):
        priority = "Critical"
    elif any(x in complaint_text.lower() for x in ['no water', 'medicine', 'broken']):
        priority = "High"
    elif "permit" in complaint_text.lower() or "meeting" in complaint_text.lower():
        priority = "Low"

    # Create the row
    row = {
        "Ticket ID": i,
        "Citizen Name": fake.name(),
        "Phone": fake.phone_number(),
        "Ward": ward,
        "Complaint Text": complaint_text,
        "Predicted Dept": dept,
        "AI Priority": priority,
        "Date Submitted": date_submitted.strftime("%Y-%m-%d %H:%M")
    }
    data.append(row)

# 5. SAVE TO CSV
df = pd.DataFrame(data)
filename = "marsabit_project_validation_data.csv"
df.to_csv(filename, index=False)

print(f"SUCCESS! Generated {len(data)} records.")
print(f"File saved as: {filename}")
print("You can open this file in Excel to show your lecturer.")