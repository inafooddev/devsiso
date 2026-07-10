import os

filepath = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\SkbRwo\Components\DetailModal.tsx"
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Add fragment open
search_open = """                                        {/* Tab Content: Pencapaian */}
                                        {activeTab === 'pencapaian' && (
                                            <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm animate-fade-in mx-1">"""
replace_open = """                                        {/* Tab Content: Pencapaian */}
                                        {activeTab === 'pencapaian' && (
                                            <>
                                            <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm animate-fade-in mx-1">"""
content = content.replace(search_open, replace_open)

# Add fragment close
search_close = """                                                </div>
                                            </div>
                                        )}
                                    
                                        {/* Tab Content: History */}"""
replace_close = """                                                </div>
                                            </div>
                                            </>
                                        )}
                                    
                                        {/* Tab Content: History */}"""

# Note: The debug output showed:
#                                         )}
#                                             </div>
#                                         )}
#                                     
#                                         {/* Tab Content: History */}

search_close = """                                            </div>
                                        )}
                                    
                                        {/* Tab Content: History */}"""
replace_close = """                                            </div>
                                            </>
                                        )}
                                    
                                        {/* Tab Content: History */}"""

content = content.replace(search_close, replace_close)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Added fragments")
