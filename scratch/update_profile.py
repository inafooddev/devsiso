import sys
import re

file_path = r"c:\Users\EDP-JAWA INA\Desktop\dev\devsiso\resources\js\mobile\Pages\Profile.tsx"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update imports
if "EyeIcon" not in content:
    content = content.replace("ShieldCheckIcon, XMarkIcon", "ShieldCheckIcon, XMarkIcon, EyeIcon, EyeSlashIcon")

# 2. Add state
if "showCurrentPassword" not in content:
    content = content.replace("const [isPasswordOpen, setIsPasswordOpen] = useState(false);", "const [isPasswordOpen, setIsPasswordOpen] = useState(false);\n    const [showCurrentPassword, setShowCurrentPassword] = useState(false);\n    const [showNewPassword, setShowNewPassword] = useState(false);\n    const [showConfirmPassword, setShowConfirmPassword] = useState(false);")

# 3. Replace inputs
# Current Password
old_current = r"""                        <input \n                            type="password" \n                            value=\{passwordForm\.data\.current_password\} \n                            onChange=\{e => passwordForm\.setData\('current_password', e\.target\.value\)\} \n                            className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"\n                        />"""
new_current = """                        <div className="relative">
                            <input 
                                type={showCurrentPassword ? "text" : "password"} 
                                value={passwordForm.data.current_password} 
                                onChange={e => passwordForm.setData('current_password', e.target.value)} 
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-12 py-3 text-sm text-black focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                            />
                            <button
                                type="button"
                                onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                                className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors"
                            >
                                {showCurrentPassword ? <EyeSlashIcon className="h-5 w-5" /> : <EyeIcon className="h-5 w-5" />}
                            </button>
                        </div>"""
content = re.sub(old_current, new_current, content, flags=re.DOTALL)

# New Password
old_new = r"""                        <input \n                            type="password" \n                            value=\{passwordForm\.data\.password\} \n                            onChange=\{e => passwordForm\.setData\('password', e\.target\.value\)\} \n                            className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"\n                        />"""
new_new = """                        <div className="relative">
                            <input 
                                type={showNewPassword ? "text" : "password"} 
                                value={passwordForm.data.password} 
                                onChange={e => passwordForm.setData('password', e.target.value)} 
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-12 py-3 text-sm text-black focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                            />
                            <button
                                type="button"
                                onClick={() => setShowNewPassword(!showNewPassword)}
                                className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors"
                            >
                                {showNewPassword ? <EyeSlashIcon className="h-5 w-5" /> : <EyeIcon className="h-5 w-5" />}
                            </button>
                        </div>"""
content = re.sub(old_new, new_new, content, flags=re.DOTALL)

# Confirm Password
old_confirm = r"""                        <input \n                            type="password" \n                            value=\{passwordForm\.data\.password_confirmation\} \n                            onChange=\{e => passwordForm\.setData\('password_confirmation', e\.target\.value\)\} \n                            className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"\n                        />"""
new_confirm = """                        <div className="relative">
                            <input 
                                type={showConfirmPassword ? "text" : "password"} 
                                value={passwordForm.data.password_confirmation} 
                                onChange={e => passwordForm.setData('password_confirmation', e.target.value)} 
                                className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-12 py-3 text-sm text-black focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"
                            />
                            <button
                                type="button"
                                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors"
                            >
                                {showConfirmPassword ? <EyeSlashIcon className="h-5 w-5" /> : <EyeIcon className="h-5 w-5" />}
                            </button>
                        </div>"""
content = re.sub(old_confirm, new_confirm, content, flags=re.DOTALL)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done")
