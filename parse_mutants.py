
import json

with open('apps/admin/reports/mutation/mutation.json') as f:
    data = json.load(f)

statuses = {}
for file_path, file_data in data['files'].items():
    for mutant in file_data.get('mutants', []):
        status = mutant['status']
        statuses[status] = statuses.get(status, 0) + 1
        if status in ['Survived', 'NoCoverage']:
            mutator_name = mutant.get('mutatorName', 'unknown')
            replacement = mutant.get('replacement', 'unknown')
            location = mutant.get('location', {}).get('start', {})
            print(f"Status: {status}, File: {file_path}, Mutator: {mutator_name}, Line: {location.get('line')}, Rep: {replacement}")

print("\nStatus counts:", statuses)
