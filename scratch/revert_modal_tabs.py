import os
import re

filepath = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\SkbRwo\Components\DetailModal.tsx"
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# We want to move the "History Order Card" section back to Pencapaian Tab.
# Find the end of Tab Content: Pencapaian
search_pencapaian_end = """                                            </div>
                                        )}
                                    
                                        {/* Tab Content: History */}"""

replace_pencapaian_end = """
                                                {/* History Order Card */}
                                                <div className="bg-slate-50 border border-slate-100 rounded-2xl p-4 mt-6 shadow-sm">
                                                    <div className="flex items-center gap-2 mb-3">
                                                        <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                                                        <h5 className="text-[11px] font-black text-slate-700 uppercase tracking-widest">History Order (Kuartal Ini)</h5>
                                                    </div>
                                                    {isLoadingHistory ? (
                                                        <div className="flex justify-center p-4">
                                                            <ArrowPathIcon className="w-5 h-5 animate-spin text-slate-400" />
                                                        </div>
                                                    ) : Object.keys(groupedHistory).length > 0 ? (
                                                        <div className="flex flex-col gap-4">
                                                            {Object.entries(groupedHistory).map(([month, groupData]) => (
                                                                <div key={month} className="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                                                                    <div className="bg-slate-50 px-3 py-2 border-b border-slate-100 flex justify-between items-center">
                                                                        <span className="text-[10px] font-bold text-slate-700 uppercase tracking-wider">{month}</span>
                                                                        <span className="text-[11px] font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(groupData.total)}</span>
                                                                    </div>
                                                                    <div className="flex flex-col">
                                                                        {groupData.items.map((item, idx) => (
                                                                            <div key={idx} className="flex justify-between items-center px-3 py-2 border-b border-slate-50 last:border-0">
                                                                                <span className="text-[10px] font-semibold text-slate-500">
                                                                                    {new Date(item.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                                                                                </span>
                                                                                <span className="text-[10px] font-bold text-slate-700">
                                                                                    Rp {new Intl.NumberFormat('id-ID').format(Number(item.value_order))}
                                                                                </span>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    ) : (
                                                        <div className="text-center p-4 border border-dashed border-slate-200 rounded-xl bg-slate-50 mb-4">
                                                            <span className="text-[10px] text-slate-400 font-medium">Belum ada history order di kuartal ini.</span>
                                                        </div>
                                                    )}

                                                    {/* Grand Total History Order */}
                                                    {!isLoadingHistory && historyOrder.length > 0 && (
                                                        <div className="pt-3 border-t border-slate-200 mt-4">
                                                            <div className="flex justify-between items-center">
                                                                <span className="text-[11px] font-black text-slate-600 uppercase tracking-wider">Total Keseluruhan</span>
                                                                <span className="text-sm font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(totalHistoryOrder)}</span>
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                    
                                        {/* Tab Content: History */}"""

content = content.replace(search_pencapaian_end, replace_pencapaian_end)


# Remove History Order Card from Tab Content: History
search_history_tab = """                                                {/* History Order Card */}
                                                <div className="bg-slate-50 border border-slate-100 rounded-2xl p-4 shadow-sm">
                                                    <div className="flex items-center gap-2 mb-3">
                                                        <ChartBarIcon className="w-4 h-4 text-indigo-600" />
                                                        <h5 className="text-[11px] font-black text-slate-700 uppercase tracking-widest">History Order (Kuartal Ini)</h5>
                                                    </div>
                                                    {isLoadingHistory ? (
                                                        <div className="flex justify-center p-4">
                                                            <ArrowPathIcon className="w-5 h-5 animate-spin text-slate-400" />
                                                        </div>
                                                    ) : Object.keys(groupedHistory).length > 0 ? (
                                                        <div className="flex flex-col gap-4">
                                                            {Object.entries(groupedHistory).map(([month, groupData]) => (
                                                                <div key={month} className="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                                                                    <div className="bg-slate-50 px-3 py-2 border-b border-slate-100 flex justify-between items-center">
                                                                        <span className="text-[10px] font-bold text-slate-700 uppercase tracking-wider">{month}</span>
                                                                        <span className="text-[11px] font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(groupData.total)}</span>
                                                                    </div>
                                                                    <div className="flex flex-col">
                                                                        {groupData.items.map((item, idx) => (
                                                                            <div key={idx} className="flex justify-between items-center px-3 py-2 border-b border-slate-50 last:border-0">
                                                                                <span className="text-[10px] font-semibold text-slate-500">
                                                                                    {new Date(item.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                                                                                </span>
                                                                                <span className="text-[10px] font-bold text-slate-700">
                                                                                    Rp {new Intl.NumberFormat('id-ID').format(Number(item.value_order))}
                                                                                </span>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    ) : (
                                                        <div className="text-center p-4 border border-dashed border-slate-200 rounded-xl bg-slate-50 mb-4">
                                                            <span className="text-[10px] text-slate-400 font-medium">Belum ada history order di kuartal ini.</span>
                                                        </div>
                                                    )}

                                                    {/* Grand Total History Order */}
                                                    {!isLoadingHistory && historyOrder.length > 0 && (
                                                        <div className="pt-3 border-t border-slate-200 mt-4">
                                                            <div className="flex justify-between items-center">
                                                                <span className="text-[11px] font-black text-slate-600 uppercase tracking-wider">Total Keseluruhan</span>
                                                                <span className="text-sm font-black text-indigo-600">Rp {new Intl.NumberFormat('id-ID').format(totalHistoryOrder)}</span>
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>"""

content = content.replace(search_history_tab, "")

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Moved History Order to Pencapaian Tab")
