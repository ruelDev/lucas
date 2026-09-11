export default function AppLogoBanner(company) {
    return (
        <img
            className={company.company === 'BFC' ? 'h-18' : ''}
            src={company.company === 'BFC' ? '/assets/images/bfc-banner.png' : '/assets/images/bmi-banner-no-bg.png'}
            alt="Banner"
        />
    );
}
