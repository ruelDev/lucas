/* eslint-disable react/prop-types */
import clsx from 'clsx';

export default function SearchInput({
    id,
    name,
    label,
    type = 'text',
    value = '',
    placeholder = '',
    required = false,
    disabled = false,
    readOnly = false,
    autoComplete = 'off',
    error = '',
    inputRef,
    onChange,
    onBlur,
    onFocus,
    onKeyDown,
}) {
    return (
        <div className="flex w-full flex-col gap-1.5">
            {/* Label */}

            <label htmlFor={id} className="flex items-center gap-1 text-sm font-medium dark:text-white text-slate-700">
                {label}

                {required && <span className="text-red-500">*</span>}
            </label>

            {/* Input */}

            <input
                ref={inputRef}
                id={id}
                name={name}
                type={type}
                value={value}
                placeholder={placeholder}
                disabled={disabled}
                readOnly={readOnly}
                autoComplete={autoComplete}
                aria-invalid={!!error}
                aria-describedby={error ? `${id}-error` : undefined}
                onChange={(e) => onChange?.(e.target.value)}
                onBlur={onBlur}
                onFocus={onFocus}
                onKeyDown={onKeyDown}
                className={clsx(
                    'w-full',
                    'rounded-xl',
                    'border',
                    'px-4',
                    'py-2.5',
                    'text-sm',
                    'transition-all',
                    'duration-200',
                    'outline-none',

                    'placeholder:text-slate-400',

                    disabled ? 'cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white',

                    error
                        ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100'
                        : 'border-gray-300 focus:border-blue-800 focus:ring-4 focus:ring-blue-100',
                )}
            />

            {/* Error */}

            {error && (
                <p id={`${id}-error`} className="text-xs text-red-600">
                    {error}
                </p>
            )}
        </div>
    );
}
