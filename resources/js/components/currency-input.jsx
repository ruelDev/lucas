import { PhilippinePeso } from 'lucide-react';

/* eslint-disable react/prop-types */
export default function CurrencyInput({ value, onChange, placeholder, className = '' }) {
    const formatCurrency = (val) => {
        if (!val) return '';
        const cleanValue = val.toString().replace(/[^0-9.]/g, '');
        const parts = cleanValue.split('.');
        let formatted = parts[0];
        if (parts.length > 1) {
            formatted = parts[0] + '.' + parts[1].slice(0, 2);
        }
        const numberPart = formatted.split('.')[0];
        const decimalPart = formatted.includes('.') ? '.' + formatted.split('.')[1] : '';
        const withSeparators = numberPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return withSeparators + decimalPart;
    };

    const parseToNumber = (formattedValue) => {
        const cleaned = formattedValue.toString().replace(/[^0-9.]/g, '');
        return cleaned === '' ? '' : cleaned;
    };

    const handleChange = (e) => {
        const numeric = parseToNumber(e.target.value);
        onChange(numeric);
    };

    const handleKeyDown = (e) => {
        const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
        if (allowedKeys.includes(e.key)) return;
        if ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x'].includes(e.key.toLowerCase())) return;
        if (!/^[0-9.]$/.test(e.key)) {
            e.preventDefault();
            return;
        }
        if (e.key === '.' && e.target.value.includes('.')) {
            e.preventDefault();
        }
    };

    const handleBlur = () => {
        const valueStr = value?.toString();

        if (!valueStr) return;

        if (!valueStr.includes('.')) {
            onChange(value + '.00');
        } else {
            const decimalPart = valueStr.split('.')[1];
            if (decimalPart?.length === 1) {
                onChange(value + '0');
            }
        }
    };

    return (
        <div className="relative">
            {/* Peso Icon */}
            <div className="pointer-events-none absolute top-5 left-3 -translate-y-1/2 text-neutral-500">
                <PhilippinePeso className="size-4" />
            </div>

            <input
                type="text"
                inputMode="decimal"
                value={formatCurrency(value)}
                onChange={handleChange}
                onKeyDown={handleKeyDown}
                onBlur={handleBlur}
                placeholder={placeholder}
                className={`pl-8 placeholder:text-sm ${className}`}
            />
        </div>
    );
}
