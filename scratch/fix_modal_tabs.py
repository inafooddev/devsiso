import os

filepath = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\SkbRwo\Components\DetailModal.tsx"
with open(filepath, 'r', encoding='utf-8') as f:
    lines = f.readlines()

pencapaian_end_index = -1
history_start_index = -1
history_end_index = -1

for i, line in enumerate(lines):
    if "                                        {/* Tab Content: History */}" in line:
        pencapaian_end_index = i - 2
    if "                                                {/* History Order Card */}" in line and i > 400 and i < 500:
        history_start_index = i
    if "                                                </div>" in line and i > 470 and i < 480:
        if "                                            </div>" in lines[i+1]:
            history_end_index = i

# Move lines from history_start_index to history_end_index into pencapaian_end_index
if history_start_index != -1 and history_end_index != -1 and pencapaian_end_index != -1:
    history_lines = lines[history_start_index:history_end_index+1]
    
    # Adjust indentation
    # Currently at 48 spaces (12 tabs/indents), need to be at 40 spaces (10 indents) or whatever the pencapaian card is.
    # Actually, the div class is `bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm`
    # Let's just insert it before line 409
    
    del lines[history_start_index:history_end_index+1]
    
    # Recalculate pencapaian_end_index after deletion because pencapaian_end_index is before history_start_index.
    # Oh wait, pencapaian_end_index is before history_start_index, so it's not affected.
    
    # To be safe, find where activeTab === 'pencapaian' ends.
    # The end is at line 409: `                                        </div>`
    for i, line in enumerate(lines):
        if "                                        {/* Tab Content: History */}" in line:
            insert_idx = i - 2
            break
            
    # Remove one layer of indentation (4 spaces) from history_lines to match pencapaian
    adjusted_history_lines = []
    for hl in history_lines:
        if hl.startswith("    "):
            adjusted_history_lines.append(hl[4:])
        else:
            adjusted_history_lines.append(hl)
            
    lines = lines[:insert_idx] + adjusted_history_lines + lines[insert_idx:]
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.writelines(lines)
    print("Successfully moved History Order block")
else:
    print(f"Could not find indices: {history_start_index}, {history_end_index}, {pencapaian_end_index}")
