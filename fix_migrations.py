import os
import re

directory = r"c:\Users\User\Desktop\dev\devsiso\database\migrations"

pattern = re.compile(r"^([ \t]*)Schema::create\s*\(\s*(.*?)\s*,\s*(?:static\s+)?function\s*\(Blueprint\s+\$table\)(?:\s+use\s*\([^)]+\))?\s*\{", re.MULTILINE)

modified_count = 0

for filename in os.listdir(directory):
    if not filename.endswith('.php'): continue
    filepath = os.path.join(directory, filename)
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    pos = 0
    new_content = ""
    modified = False
    
    while True:
        match = pattern.search(content, pos)
        if not match:
            new_content += content[pos:]
            break
            
        start_idx = match.start()
        indent = match.group(1)
        table_arg = match.group(2).strip()
        
        # Check if already wrapped
        preceding = content[max(0, start_idx-200):start_idx]
        # We need to extract the table name cleanly in case of quotes.
        # simple check: if `hasTable` is in preceding string, we might want to verify.
        if 'hasTable' in preceding:
            # check if it's actually wrapping this table
            # it might be wrapping a previous table if they are close.
            # let's be careful. Let's just check if `hasTable(` + table_arg is there.
            table_str_for_check = table_arg.replace('"', "'")
            if table_str_for_check in preceding.replace('"', "'"):
                new_content += content[pos:match.end()]
                pos = match.end()
                continue
            
        modified = True
        
        new_content += content[pos:start_idx]
        
        # Find the matching closing bracket
        brace_level = 1
        i = match.end()
        while i < len(content):
            if content[i] == '{':
                brace_level += 1
            elif content[i] == '}':
                brace_level -= 1
                if brace_level == 0:
                    break
            i += 1
            
        # closing is at `i`. Let's consume `);` if present
        end_idx = i + 1
        if end_idx < len(content) and content[end_idx] == ')':
            end_idx += 1
            if end_idx < len(content) and content[end_idx] == ';':
                end_idx += 1
                
        block = content[match.start():end_idx]
        
        # We need to indent every line of the block
        block_lines = block.split('\n')
        indented_lines = []
        for idx, line in enumerate(block_lines):
            if idx == 0:
                indented_lines.append(indent + "    " + line.lstrip())
            else:
                if line.strip() == '':
                    indented_lines.append(line)
                else:
                    indented_lines.append("    " + line)
                    
        wrapped_block = f"{indent}if (!Schema::hasTable({table_arg})) {{\n" + '\n'.join(indented_lines) + f"\n{indent}}}"
        new_content += wrapped_block
        pos = end_idx
        
    if modified:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Modified {filename}")
        modified_count += 1

print(f"Total modified files: {modified_count}")
