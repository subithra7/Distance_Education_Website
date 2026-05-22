import codecs
import re

out = ["DROP SCHEMA public CASCADE;\nCREATE SCHEMA public;\n"]
in_insert = False

with codecs.open('admission_db_postgres.sql', 'r', 'utf-8') as f:
    for line in f:
        if line.startswith('INSERT INTO'):
            if not line.strip().endswith(';'):
                in_insert = True
            continue
        if in_insert:
            if line.strip().endswith(';'):
                in_insert = False
            continue
            
        line = line.replace('tinyint', 'smallint')
        line = line.replace('TINYINT', 'smallint')
        
        line = re.sub(r'ADD\s+UNIQUE\s+KEY\s+"([^"]+)"\s*\("([^"]+)"\)', r'ADD UNIQUE ("\2")', line, flags=re.IGNORECASE)
        
        if 'MODIFY "id" SERIAL' in line or 'AUTO_INCREMENT=' in line:
            continue
            
        out.append(line)

with codecs.open('schema_only.sql', 'w', 'utf-8') as f:
    f.write("".join(out))
