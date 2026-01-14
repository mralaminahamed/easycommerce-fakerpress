import * as React from 'react';

import { cn } from '@/admin/lib/utils';

const Input = React.forwardRef<HTMLInputElement, React.ComponentProps<'input'>>(
	( { className, type, ...props }, ref ) => {
		return (
			<input
				type={ type }
				className={ cn(
					'flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-gray-900 placeholder:text-gray-500 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
					className,
				) }
				ref={ ref }
				{ ...props }
			/>
		);
	},
);
Input.displayName = 'Input';

export { Input };
