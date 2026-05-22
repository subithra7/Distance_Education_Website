import re

def convert_sql(input_file, output_file):
    with open(input_file, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
        
    out_lines = []
    in_create_table = False
    
    for line in lines:
        if line.startswith('/*!'):
            continue
        if line.startswith('SET SQL_MODE') or line.startswith('SET time_zone') or line.startswith('START TRANSACTION'):
            continue
            
        # Basic cleanup
        line = re.sub(r'ENGINE=InnoDB.*?;', ';', line, flags=re.IGNORECASE)
        line = re.sub(r'(?i)ON UPDATE\s+CURRENT_TIMESTAMP\(\)', '', line)
        line = re.sub(r'(?i)current_timestamp\(\)', 'CURRENT_TIMESTAMP', line)
        line = line.replace('`', '"')
        
        # Types
        line = re.sub(r'(?i)int\(\d+\)', 'INT', line)
        line = re.sub(r'(?i)tinyint\(1\)', 'BOOLEAN', line)
        line = re.sub(r'(?i)datetime', 'TIMESTAMP', line)
        line = re.sub(r'(?i)longtext', 'TEXT', line)
        
        # Auto increment logic
        # Replace int NOT NULL AUTO_INCREMENT with SERIAL
        if 'AUTO_INCREMENT' in line.upper():
            line = re.sub(r'(?i)int\s+NOT NULL\s+AUTO_INCREMENT', 'SERIAL', line)
            line = re.sub(r'(?i)int\s+AUTO_INCREMENT', 'SERIAL', line)
        
        out_lines.append(line)
        
    with open(output_file, 'w', encoding='utf-8') as f:
        f.writelines(out_lines)

convert_sql('admission_db (10).sql', 'admission_db_postgres.sql')
print('Conversion complete.')
