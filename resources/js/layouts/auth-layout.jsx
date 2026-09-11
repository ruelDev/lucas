import AuthLayoutTemplate from '@/layouts/auth/auth-card-layout';

export default function AuthLayout({ children, title, description, ...props }) {
    return (
        <AuthLayoutTemplate title={title} description={description} {...props}
            style={{
                backgroundImage: `url('/assets/images/bmi-hero.jpg)`
            }}
        >
            {children}
        </AuthLayoutTemplate>
    );
}
