import AppLogoBanner from './app-logo-banner';
import AppLogoIcon from './app-logo-icon';

export default function AppLogo({ sidebarOpen, company }) {
    return (
        <>
            {!sidebarOpen ? (
                <div className="aspect-square size-8 rounded-md">
                    <AppLogoIcon />
                </div>
            ) : (
                <div className='dark:bg-neutral-50 p-2'>
                    <AppLogoBanner company={company} />
                </div>
            )}
        </>
    );
}
