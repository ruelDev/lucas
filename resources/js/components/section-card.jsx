/* eslint-disable react/prop-types */
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';

export default function SectionCard({ step, icon: Icon, title, description, children }) {
    return (
        <Card className="mb-4">
            <CardHeader className="pb-3">
                <div className="flex items-center gap-3">
                    <div className="flex h-8 w-8 items-center justify-center rounded-md bg-blue-50 dark:bg-blue-950">
                        <Icon className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div className="flex-1">
                        <CardTitle className="text-sm font-medium">{title}</CardTitle>
                        <CardDescription className="text-xs">{description}</CardDescription>
                    </div>
                    <span className="flex h-5 w-5 items-center justify-center rounded-full bg-blue-50 text-xs font-medium text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                        {step}
                    </span>
                </div>
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}
