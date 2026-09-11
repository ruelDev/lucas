import { forwardRef, useState } from "react";
import { Input } from "./ui/input";
import { Eye, EyeOff } from "lucide-react";



const PasswordInput = forwardRef(({ className = '', ...props }, ref) => {
    const [showPassword, setShowPassword] = useState(false);

    const togglePasswordVisibility = () => {
        setShowPassword(!showPassword);
    }

    return (
        <div className="relative">
            <Input
                {...props}
                ref={ref}
                type={showPassword ? "text" : "password"}
                className={`pr-10 [&::-ms-reveal]:hidden [&::-ms-clear]:hidden ${className}`}
            />
            <button
                type="button"
                onClick={togglePasswordVisibility}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                tabIndex={-1}
                aria-label={showPassword ? "Hide Password" : "Show Password"}
            >
                {showPassword ? (
                    <Eye className="h-4 w-4" />
                ) : (
                    <EyeOff className="h-4 w-4" />
                )}
            </button>
        </div>
    );
});

PasswordInput.displayName = 'PasswordInput';

export default PasswordInput;