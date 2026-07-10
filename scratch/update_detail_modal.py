import os

filepath = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\SkbRwo\Components\DetailModal.tsx"
with open(filepath, 'r') as f:
    content = f.read()

# Add axios
content = content.replace("import { router } from '@inertiajs/react';", "import { router } from '@inertiajs/react';\nimport axios from 'axios';")

# Add state
state_block = """    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isLocating, setIsLocating] = useState(false);
    const [showCloseConfirm, setShowCloseConfirm] = useState(false);

    const [historyOrder, setHistoryOrder] = useState<any[]>([]);
    const [isLoadingHistory, setIsLoadingHistory] = useState(false);

    const ktpRef = useRef<HTMLInputElement>(null);"""

content = content.replace("""    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isLocating, setIsLocating] = useState(false);
    const [showCloseConfirm, setShowCloseConfirm] = useState(false);

    const ktpRef = useRef<HTMLInputElement>(null);""", state_block)

# Add effect for fetching history
effect_block = """
    useEffect(() => {
        if (data && isMonitoring) {
            setIsLoadingHistory(true);
            axios.get(`/mobile/skb-rwo/history-order/${data.customer_code}`)
                .then(res => {
                    setHistoryOrder(res.data || []);
                })
                .catch(err => {
                    console.error('Failed to fetch history', err);
                })
                .finally(() => {
                    setIsLoadingHistory(false);
                });
        }
    }, [data, isMonitoring]);

    const groupedHistory = React.useMemo(() => {
        if (!historyOrder || historyOrder.length === 0) return {};
        const groups: { [month: string]: { total: number, items: any[] } } = {};
        
        historyOrder.forEach(item => {
            const date = new Date(item.tanggal);
            const monthName = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            if (!groups[monthName]) {
                groups[monthName] = { total: 0, items: [] };
            }
            groups[monthName].items.push(item);
            groups[monthName].total += Number(item.value_order);
        });
        
        return groups;
    }, [historyOrder]);

    const isEditingRef = useRef(isEditing);"""

content = content.replace("    const isEditingRef = useRef(isEditing);", effect_block)

# Add rendering of history order
history_render = """                                        </div>
                                        
                                        {/* History Order */}
                                        <div className="pt-4 border-t border-slate-200 mt-4">
                                            <h5 className="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">History Order (Kuartal Ini)</h5>
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
                                                                    <div key={idx} className={`flex justify-between items-center px-3 py-2 ${idx !== groupData.items.length - 1 ? 'border-b border-slate-50' : ''}`}>
                                                                        <span className="text-[10px] font-medium text-slate-500">{new Date(item.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                                                                        <span className="text-[10px] font-bold text-slate-700">Rp {new Intl.NumberFormat('id-ID').format(item.value_order)}</span>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : (
                                                <div className="text-center p-4 border border-dashed border-slate-200 rounded-xl bg-slate-50">
                                                    <span className="text-[10px] text-slate-400 font-medium">Belum ada history order di kuartal ini.</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>"""

content = content.replace("                                        </div>\n                                    </div>", history_render)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated DetailModal")
