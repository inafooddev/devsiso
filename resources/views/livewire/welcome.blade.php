<div class="min-h-[80vh] flex flex-col items-center justify-center p-6 bg-base-200">
    <div class="max-w-4xl w-full bg-base-100 rounded-3xl shadow-2xl overflow-hidden border border-base-200/60 p-8 md:p-12 text-center relative backdrop-blur-sm">
        <!-- Decorative subtle background glow -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-secondary/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <!-- Sleek Icon -->
            <div class="w-20 h-20 bg-gradient-to-tr from-primary to-secondary rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-primary/20 text-white">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143Z" />
                </svg>
            </div>

            <!-- Greeting Typography -->
            <div class="space-y-2">
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight">
                    Welcome back, <span class="bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">{{ auth()->user()->name ?? 'User' }}</span>
                </h1>
            </div>
        </div>
    </div>
</div>
