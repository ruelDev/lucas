/* eslint-disable react/prop-types */
import { Card, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';

export default function UserCounts({ userData }) {

    const cards = [
        {
            label: 'Total Users',
            value: userData.totalCount,
            footerMain: `${(userData.newUsersLastMonthPercentage ?? 0).toFixed(2)} % users added from this month`,
            footerSub: 'All registered users',
        },
        {
            label: 'New Users Today',
            value: userData.newUsersToday,
            footerMain: `+${(userData.todayYesterdayDiffPercentage ?? 0).toFixed(2)} % compared to yesterday`,
            footerSub: 'Registered today',
        },
        {
            label: 'Active Users',
            value: userData.activeUsers,
            footerMain: 'Engaged users',
            footerSub: 'Last 30 days',
        },
        {
            label: 'Inactive Users',
            value: userData.inactiveUsers,
            footerMain: 'Needs re-engagement',
            footerSub: 'No activity in 30 days',
        },
    ];

    return (
        <div className="grid w-full grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4">
            {cards.map((card) => (
                <Card key={card.label} className="border-brand-primary @container/card border-l-4">
                    <CardHeader className="space-y-1 pb-2">
                        <CardDescription className="text-brand-primary/80 font-medium">
                            {card.label}
                        </CardDescription>

                        <CardTitle className="text-brand-primary text-3xl font-bold tracking-tight tabular-nums">
                            {Number(card.value).toLocaleString()}
                        </CardTitle>
                    </CardHeader>

                    <CardFooter className="flex-col items-start gap-1 text-sm">
                        <div className="text-brand-accent font-semibold">
                            {card.footerMain}
                        </div>
                        <div className="text-muted-foreground">
                            {card.footerSub}
                        </div>
                    </CardFooter>
                </Card>
            ))}
        </div>
    );
}