/* eslint-disable react/prop-types */

export default function SectionHeader({ icon, iconBg, iconColor, title, description }) {
    return (
        <div className="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <div className={`flex h-8 w-8 items-center justify-center rounded-lg ${iconBg}`}>
                <span className={iconColor}>{icon}</span>
            </div>
            <div>
                <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">{title}</h3>
                {description && (
                    <p className="text-xs text-gray-500 dark:text-gray-400">{description}</p>
                )}
            </div>
        </div>
    );
}