import sys
import re

file_path = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\SkbRwo\Index.tsx"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. Add import
if "import ScrollCalendar" not in content:
    content = content.replace("import SearchBar from '../../Components/UI/SearchBar';", "import SearchBar from '../../Components/UI/SearchBar';\nimport ScrollCalendar from '../../Components/UI/ScrollCalendar';")

# 2. Remove getDaysInMonth and handlePrevMonth block
content = re.sub(
    r"    const getDaysInMonth = \(date: Date\) => \{.*?    const handleNextMonth = \(\) => [^\n]+;\n",
    "",
    content,
    flags=re.DOTALL
)

# 3. Remove scroll useEffect
content = re.sub(
    r"    useEffect\(\(\) => \{\n        if \(activeTab === 'plan'\) \{\n            setTimeout\(\(\) => \{\n                const el = document\.getElementById\('selected-date-btn'\);\n                if \(el\) el\.scrollIntoView\(\{ behavior: 'smooth', inline: 'center', block: 'nearest' \}\);\n            \}, 50\);\n        \}\n    \}, \[activeTab, selectedDate\]\);\n\n",
    "",
    content,
    flags=re.DOTALL
)

# 4. Replace inline calendar with ScrollCalendar
old_calendar_block = r"                \{activeTab === 'plan' && \(\n                    <div className=\"bg-white pt-2 pb-2 border-t border-slate-100 flex flex-col gap-3 px-4\">\n.*?                        </div>\n                    </div>\n                \)\}"

new_calendar_block = """                {activeTab === 'plan' && (
                    <div className="bg-white pt-3 border-t border-slate-100">
                        <ScrollCalendar 
                            selectedDate={selectedDate} 
                            setSelectedDate={setSelectedDate} 
                            markedDates={listPlan.map(p => p.tanggal).filter(Boolean) as string[]} 
                        />
                    </div>
                )}"""

content = re.sub(old_calendar_block, new_calendar_block, content, flags=re.DOTALL)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done")
