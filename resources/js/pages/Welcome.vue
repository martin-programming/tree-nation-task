<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { DashboardStats, VisitsByHour } from '@/types';

const props = defineProps<DashboardStats>();

const maxCount = computed(() =>
    props.visitsByHour.length > 0 ? Math.max(...props.visitsByHour.map((v) => v.count)) : 1,
);

function formatHour(iso: string): string {
    return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
}

function barWidth(row: VisitsByHour): string {
    const pct = maxCount.value > 0 ? Math.round((row.count / maxCount.value) * 100) : 0;
    return `${pct}%`;
}
</script>

<template>
    <Head title="Tree Nation — Visit Dashboard" />

    <div class="min-h-screen bg-background text-foreground">
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-bold tracking-tight">Tree Nation</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Live visit tracker — every {{ 10 }} visits plants a tree
                </p>
            </div>

            <!-- Summary cards -->
            <div class="mb-10 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <p class="text-sm font-medium text-muted-foreground">Visits today</p>
                    <p class="mt-2 text-4xl font-bold tabular-nums">{{ totalVisitsToday }}</p>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <p class="text-sm font-medium text-muted-foreground">Trees planted</p>
                    <p class="mt-2 text-4xl font-bold tabular-nums text-green-600 dark:text-green-400">
                        {{ totalTreesPlanted }}
                    </p>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <p class="text-sm font-medium text-muted-foreground">Customers</p>
                    <p class="mt-2 text-4xl font-bold tabular-nums">{{ totalCustomers }}</p>
                </div>
            </div>

            <!-- Visits per hour -->
            <div class="rounded-xl border bg-card shadow-sm">
                <div class="border-b px-6 py-4">
                    <h2 class="text-base font-semibold">Visits per hour <span class="ml-1 text-xs font-normal text-muted-foreground">(last 24 h)</span></h2>
                </div>

                <div v-if="visitsByHour.length === 0" class="px-6 py-12 text-center text-sm text-muted-foreground">
                    No visits recorded in the last 24 hours.
                </div>

                <ul v-else class="divide-y">
                    <li
                        v-for="row in visitsByHour"
                        :key="row.hour"
                        class="flex items-center gap-4 px-6 py-3"
                    >
                        <span class="w-12 shrink-0 text-right text-sm tabular-nums text-muted-foreground">
                            {{ formatHour(row.hour) }}
                        </span>
                        <div class="flex-1 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-3 rounded-full bg-primary transition-all"
                                :style="{ width: barWidth(row) }"
                            />
                        </div>
                        <span class="w-8 shrink-0 text-right text-sm font-medium tabular-nums">
                            {{ row.count }}
                        </span>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</template>
