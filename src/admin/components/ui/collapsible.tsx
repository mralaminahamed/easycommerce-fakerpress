import * as CollapsiblePrimitive from '@radix-ui/react-collapsible';
import * as React from 'react';

import { cn } from '@/admin/lib/utils';

const Collapsible = CollapsiblePrimitive.Root;

const CollapsibleTrigger = React.forwardRef<
  React.ElementRef<typeof CollapsiblePrimitive.CollapsibleTrigger>,
  React.ComponentPropsWithoutRef<
    typeof CollapsiblePrimitive.CollapsibleTrigger
  > & { 'aria-label'?: string }
>( ( { className, 'aria-label': ariaLabel, ...props }, ref ) => (
	<CollapsiblePrimitive.CollapsibleTrigger
		ref={ ref }
		className={ cn( 'flex', className ) }
		aria-label={ ariaLabel || 'Toggle collapsible section' }
		{ ...props }
	/>
) );
CollapsibleTrigger.displayName =
  CollapsiblePrimitive.CollapsibleTrigger.displayName;

const CollapsibleContent = React.forwardRef<
  React.ElementRef<typeof CollapsiblePrimitive.CollapsibleContent>,
  React.ComponentPropsWithoutRef<typeof CollapsiblePrimitive.CollapsibleContent>
>( ( { className, ...props }, ref ) => (
	<CollapsiblePrimitive.CollapsibleContent
		ref={ ref }
		className={ cn(
			'overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down',
			className,
		) }
		{ ...props }
	/>
) );

CollapsibleContent.displayName =
  CollapsiblePrimitive.CollapsibleContent.displayName;

export { Collapsible, CollapsibleTrigger, CollapsibleContent };
