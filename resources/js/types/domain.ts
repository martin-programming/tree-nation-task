export type VisitsByHour = {
    hour: string;
    count: number;
};

export type DashboardStats = {
    visitsByHour: VisitsByHour[];
    totalVisitsToday: number;
    totalTreesPlanted: number;
    totalCustomers: number;
};
