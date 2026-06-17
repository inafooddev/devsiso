<div class="mx-auto px-6 py-10 lg:py-16 max-w-7xl">
    <!-- Hero Welcome Section -->
    <div class="relative overflow-hidden bg-base-100 rounded-3xl shadow-xl mb-12 border border-base-200/60">
        <!-- Abstract Background Effects -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-secondary/5 pointer-events-none"></div>
        <div class="absolute -top-32 -right-32 w-[30rem] h-[30rem] bg-primary/10 rounded-full blur-[80px] pointer-events-none mix-blend-multiply opacity-70"></div>
        <div class="absolute -bottom-32 -left-32 w-[30rem] h-[30rem] bg-secondary/10 rounded-full blur-[80px] pointer-events-none mix-blend-multiply opacity-70"></div>
        
        <div class="relative z-10 p-10 md:p-14 lg:p-20 flex flex-col md:flex-row items-center justify-between gap-12 lg:gap-20">
            <!-- Text Content -->
            <div class="flex-1 space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-base-200/80 border border-base-300 text-base-content shadow-sm backdrop-blur-md">
                    <span class="text-lg">👋</span>
                    <span class="text-sm font-semibold tracking-wide uppercase text-base-content/80">Welcome to Workspace</span>
                </div>
                
                <div class="space-y-4">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-base-content leading-[1.1] tracking-tight">
                        Grow your business <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">with clarity.</span>
                    </h1>
                    <p class="text-lg md:text-xl text-base-content/70 max-w-xl leading-relaxed">
                        Your professional command center is ready. Track performance, manage outlets, and make data-driven decisions seamlessly.
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <button class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-primary-content transition-all bg-primary rounded-xl hover:bg-primary-focus hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        View Dashboard
                        <x-heroicon-m-arrow-right class="w-5 h-5 ml-2" />
                    </button>
                    <button class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold transition-all border-2 border-base-300 bg-base-100 hover:bg-base-200 text-base-content rounded-xl hover:shadow-md hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-base-300 focus:ring-offset-2">
                        <x-heroicon-m-document-text class="w-5 h-5 mr-2 text-base-content/60" />
                        Documentation
                    </button>
                </div>
            </div>
            
            <!-- Decorative Mockup Element -->
            <div class="flex-1 w-full hidden md:flex justify-center lg:justify-end">
                <div class="relative w-full max-w-md aspect-square">
                    <!-- Glow effect behind mockup -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-secondary/20 rounded-[2.5rem] transform rotate-6 scale-105 blur-2xl opacity-60 animate-pulse"></div>
                    
                    <!-- Floating Glass Card -->
                    <div class="relative w-full h-full bg-base-100/60 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] p-8 flex flex-col gap-6 transform hover:-translate-y-2 hover:rotate-2 transition-all duration-500 ease-out z-10">
                        <!-- Top Bar Mock -->
                        <div class="w-full flex items-center justify-between pb-4 border-b border-base-200/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                                    <x-heroicon-s-chart-pie class="w-6 h-6 text-primary" />
                                </div>
                                <div class="space-y-2">
                                    <div class="w-24 h-4 bg-base-300 rounded-md"></div>
                                    <div class="w-16 h-3 bg-base-200 rounded-md"></div>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-base-200"></div>
                        </div>
                        
                        <!-- Chart Mock -->
                        <div class="flex-1 flex items-end gap-3 pb-2">
                            <div class="w-1/5 h-[30%] bg-base-200 rounded-t-xl hover:bg-primary/40 transition-colors"></div>
                            <div class="w-1/5 h-[50%] bg-primary/30 rounded-t-xl hover:bg-primary/50 transition-colors"></div>
                            <div class="w-1/5 h-[80%] bg-primary/60 rounded-t-xl hover:bg-primary/70 transition-colors relative">
                                <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-base-content text-base-100 text-xs py-1 px-2 rounded font-bold shadow-lg shadow-base-content/20">+24%</div>
                            </div>
                            <div class="w-1/5 h-[65%] bg-primary/40 rounded-t-xl hover:bg-primary/50 transition-colors"></div>
                            <div class="w-1/5 h-[45%] bg-base-200 rounded-t-xl hover:bg-primary/40 transition-colors"></div>
                        </div>
                        
                        <!-- Bottom Stats Mock -->
                        <div class="flex gap-4 w-full h-24">
                            <div class="flex-1 bg-base-200/50 rounded-xl p-4 flex flex-col justify-center space-y-2">
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center">
                                    <x-heroicon-s-arrow-trending-up class="w-4 h-4 text-success" />
                                </div>
                                <div class="w-1/2 h-3 bg-base-300 rounded"></div>
                            </div>
                            <div class="flex-1 bg-base-200/50 rounded-xl p-4 flex flex-col justify-center space-y-2">
                                <div class="w-8 h-8 rounded-full bg-secondary/20 flex items-center justify-center">
                                    <x-heroicon-s-users class="w-4 h-4 text-secondary" />
                                </div>
                                <div class="w-2/3 h-3 bg-base-300 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Value Propositions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 mb-10">
        <!-- Feature 1 -->
        <div class="group bg-base-100 p-8 rounded-3xl shadow-sm border border-base-200 hover:shadow-xl hover:shadow-primary/5 hover:border-primary/20 transition-all duration-300 flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-primary-content transition-all duration-300 text-primary">
                <x-heroicon-o-chart-bar class="w-8 h-8" />
            </div>
            <h3 class="text-xl font-extrabold text-base-content mb-3">Intuitive Analytics</h3>
            <p class="text-base-content/60 leading-relaxed">
                Transform your raw data into actionable insights instantly. Monitor trends and track key performance indicators with ease.
            </p>
        </div>
        
        <!-- Feature 2 -->
        <div class="group bg-base-100 p-8 rounded-3xl shadow-sm border border-base-200 hover:shadow-xl hover:shadow-secondary/5 hover:border-secondary/20 transition-all duration-300 flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-secondary/10 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-secondary group-hover:text-secondary-content transition-all duration-300 text-secondary">
                <x-heroicon-o-building-storefront class="w-8 h-8" />
            </div>
            <h3 class="text-xl font-extrabold text-base-content mb-3">Outlet Control</h3>
            <p class="text-base-content/60 leading-relaxed">
                Centralize the management of all your outlets. From inventory updates to staff assignments, control everything effortlessly.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="group bg-base-100 p-8 rounded-3xl shadow-sm border border-base-200 hover:shadow-xl hover:shadow-accent/5 hover:border-accent/20 transition-all duration-300 flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-accent/10 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-accent group-hover:text-accent-content transition-all duration-300 text-accent">
                <x-heroicon-o-bolt class="w-8 h-8" />
            </div>
            <h3 class="text-xl font-extrabold text-base-content mb-3">Lightning Fast</h3>
            <p class="text-base-content/60 leading-relaxed">
                Experience a smooth, lag-free workflow. Our platform is optimized to give you the speed you need to run your business.
            </p>
        </div>
    </div>
</div>