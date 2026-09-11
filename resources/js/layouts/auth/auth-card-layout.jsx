import AppLogoIcon from '@/components/app-logo-icon';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/react';

export default function AuthCardLayout({ children, title, description }) {
    return (
        <div
            className="relative flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10"
            style={{
                backgroundImage: 'url(/assets/images/bmi-hero.jpg)',
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundRepeat: 'no-repeat',
            }}
        >
            {/* <div className="absolute inset-0 z-10 bg-gradient-to-br from-blue-600/30 via-blue-500/25 to-blue-700/35"></div> */}
            <div className="absolute inset-0 z-10 bg-linear-to-br from-gray-600/40 via-gray-500/55 to-gray-700/65"></div>

            <div className="relative z-20 flex w-full max-w-md flex-col gap-6">
                <div className="flex flex-col gap-6">
                    <Card>
                        <Link href={route('home')} className="flex justify-center pt-6">
                            <div className="flex h-9 w-9 items-center justify-center">
                                <AppLogoIcon className="size-9 fill-current text-black dark:text-white" />
                            </div>
                        </Link>
                        <CardHeader className="px-10 pt-8 pb-0 text-center">
                            <CardTitle className="text-xl">{title}</CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-10 py-8">{children}</CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
