import os

filepath = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\SkbRwo\Components\DetailModal.tsx"
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# I need to wrap the contents of pencapaian in a fragment, and add the closing `)}`
search = """                                        {/* Tab Content: Pencapaian */}
                                        {activeTab === 'pencapaian' && (
                                            <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm animate-fade-in mx-1">"""
replace = """                                        {/* Tab Content: Pencapaian */}
                                        {activeTab === 'pencapaian' && (
                                            <div className="animate-fade-in mx-1">
                                                <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm">"""

content = content.replace(search, replace)

# I also need to close the div and add `)}` after the History Order Card
# The History Order Card ends around line 470 with:
#                                                     </div>
#                                                 </div>
#                                             )}
#                                                 </div>
# 
#                                         {/* Tab Content: History */}

search2 = """                                                        </div>
                                                    )}
                                                </div>"""
replace2 = """                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        )}"""

# Let's find exactly how the history order card ends.
with open("scratch/debug_modal.txt", "w", encoding="utf-8") as out:
    out.write(content[content.find("Total Keseluruhan"):content.find("{/* Tab Content: History */}")])

