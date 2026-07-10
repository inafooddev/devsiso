import os
import re

filepath = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\SkbRwo\Components\DetailModal.tsx"
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Add activeTab state
state_search = "const [isEditing, setIsEditing] = useState(false);"
state_replace = "const [activeTab, setActiveTab] = useState<'pencapaian' | 'history'>('pencapaian');\n    const [isEditing, setIsEditing] = useState(false);"
if "const [activeTab" not in content:
    content = content.replace(state_search, state_replace)

# Modify the monitoring section to wrap in activeTab conditions
monitoring_start = """                                {/* Monitoring Achievement Section */}
                                {isMonitoring && (
                                    <>
                                        <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm">"""
                                        
monitoring_tabs = """                                {/* Monitoring Achievement Section */}
                                {isMonitoring && (
                                    <>
                                        {/* Tabs Navigation */}
                                        <div className="flex border-b border-slate-200 mb-4 bg-slate-50 rounded-t-2xl px-2 pt-2 mx-1 shadow-sm">
                                            <button 
                                                onClick={() => setActiveTab('pencapaian')} 
                                                className={`flex-1 py-3 text-[11px] font-black text-center uppercase tracking-widest border-b-2 transition-colors ${activeTab === 'pencapaian' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-400 hover:text-slate-600'}`}
                                            >
                                                Pencapaian
                                            </button>
                                            <button 
                                                onClick={() => setActiveTab('history')} 
                                                className={`flex-1 py-3 text-[11px] font-black text-center uppercase tracking-widest border-b-2 transition-colors ${activeTab === 'history' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-400 hover:text-slate-600'}`}
                                            >
                                                History
                                            </button>
                                        </div>

                                        {/* Tab Content: Pencapaian */}
                                        {activeTab === 'pencapaian' && (
                                            <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm animate-fade-in mx-1">"""

if "Tabs Navigation" not in content:
    content = content.replace(monitoring_start, monitoring_tabs)

# History Order Card section
history_start = """                                    </div>
                                    
                                    {/* History Order Card */}
                                    <div className="mb-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm">
                                            <div className="flex items-center gap-2 mb-3">
                                                <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                                                <h5 className="text-[11px] font-black text-slate-700 uppercase tracking-widest">History Order (Kuartal Ini)</h5>
                                            </div>"""
                                            
history_tab = """                                        </div>
                                        )}
                                    
                                        {/* Tab Content: History */}
                                        {activeTab === 'history' && (
                                            <div className="flex flex-col gap-4 animate-fade-in mb-6 mx-1">
                                                {/* Transaction Stats */}
                                                <div className="grid grid-cols-2 gap-3">
                                                    <div className="bg-white border border-slate-200 rounded-xl p-3 flex flex-col justify-center shadow-sm">
                                                        <span className="text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-1">Last Transaction</span>
                                                        <span className="text-xs font-black text-indigo-700">{data.last_transaction_date ? new Date(data.last_transaction_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'}</span>
                                                    </div>
                                                    <div className="bg-white border border-slate-200 rounded-xl p-3 flex flex-col justify-center shadow-sm">
                                                        <span className="text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-1">Max Transaction</span>
                                                        <span className="text-xs font-black text-indigo-700">Rp {new Intl.NumberFormat('id-ID').format(data.max_transaction || 0)}</span>
                                                    </div>
                                                    <div className="col-span-2 bg-white border border-slate-200 rounded-xl p-3 flex justify-between items-center shadow-sm">
                                                        <span className="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Average Transaction</span>
                                                        <span className="text-sm font-black text-indigo-700">Rp {new Intl.NumberFormat('id-ID').format(data.avg_transaction || 0)}</span>
                                                    </div>
                                                </div>

                                                {/* History Order Card */}
                                                <div className="bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm">
                                                    <div className="flex items-center gap-2 mb-3">
                                                        <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                                                        <h5 className="text-[11px] font-black text-slate-700 uppercase tracking-widest">History Order (Kuartal Ini)</h5>
                                                    </div>"""

if "Tab Content: History" not in content:
    content = content.replace(history_start, history_tab)

# Closing tag
history_end = """                                                </div>
                                            )}
                                        </div>
                                    </>
                                )}"""
                                
history_end_replace = """                                                </div>
                                            )}
                                                </div>
                                            </div>
                                        )}
                                    </>
                                )}"""
                                
if "Tab Content: History" in content and "Tab Content: History" not in open(filepath).read():
    content = content.replace(history_end, history_end_replace)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated DetailModal with Tabs and Stats")
