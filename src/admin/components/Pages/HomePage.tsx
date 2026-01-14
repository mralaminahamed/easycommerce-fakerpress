import { motion, Variants } from 'framer-motion';
import { Star, ArrowRight } from 'lucide-react';
import React from 'react';
import { Link } from 'react-router-dom';

import { __, sprintf } from '@wordpress/i18n';

import { Badge } from '@/admin/components/ui/badge';
import {
	Card,
	CardContent,
	CardDescription,
	CardHeader,
	CardTitle,
} from '@/admin/components/ui/card';
import { generators, type Generator } from '@/admin/lib/generators';

export default function HomePage() {
	const groupedGenerators = generators.reduce(
		( acc, generator ) => {
			if ( ! acc[ generator.category ] ) {
				acc[ generator.category ] = [];
			}
			acc[ generator.category ].push( generator );
			return acc;
		},
    {} as Record<string, Generator[]>,
	);

	const sortedCategories = Object.keys( groupedGenerators ).sort( ( a, b ) => {
		// Put "Core" category first
		if ( a === __( 'Core', 'easycommerce-fakerpress' ) ) {
			return -1;
		}
		if ( b === __( 'Core', 'easycommerce-fakerpress' ) ) {
			return 1;
		}
		// Then sort alphabetically
		return a.localeCompare( b );
	} );

	// Animation variants
	const containerVariants: Variants = {
		hidden: { opacity: 0 },
		visible: {
			opacity: 1,
			transition: {
				staggerChildren: 0.1,
				delayChildren: 0.2,
			},
		},
	};

	const categoryVariants: Variants = {
		hidden: { opacity: 0, y: 20 },
		visible: {
			opacity: 1,
			y: 0,
			transition: {
				duration: 0.6,
				ease: [ 0.4, 0.0, 0.2, 1 ],
			},
		},
	};

	const cardVariants: Variants = {
		hidden: { opacity: 0, y: 30, scale: 0.95 },
		visible: {
			opacity: 1,
			y: 0,
			scale: 1,
			transition: {
				duration: 0.5,
				ease: [ 0.4, 0.0, 0.2, 1 ],
			},
		},
		hover: {
			y: -8,
			scale: 1.02,
			transition: {
				duration: 0.2,
				ease: [ 0.4, 0.0, 0.2, 1 ],
			},
		},
	};

	return (
		<motion.div
			className="max-w-7xl mx-auto px-6 space-y-12"
			variants={ containerVariants }
			initial="hidden"
			animate="visible"
		>
			{ sortedCategories.map( ( category, categoryIndex ) => (
				<motion.div
					key={ category }
					variants={ categoryVariants }
					className="space-y-6"
				>
					<motion.div
						className="text-center"
						initial={ { opacity: 0, y: -20 } }
						animate={ { opacity: 1, y: 0 } }
						transition={ { delay: categoryIndex * 0.1 + 0.3 } }
					>
						<h2 className="text-3xl font-bold text-gray-900 mb-2">
							{ sprintf(
								__( '%s Generators', 'easycommerce-fakerpress' ),
								category,
							) }
						</h2>
						<motion.div
							className="h-1 w-24 bg-linear-to-r from-blue-500 to-purple-600 rounded-full mx-auto"
							initial={ { width: 0 } }
							animate={ { width: 96 } }
							transition={ { delay: categoryIndex * 0.1 + 0.5, duration: 0.8 } }
						/>
					</motion.div>

					<motion.div
						className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
						variants={ containerVariants }
					>
						{ groupedGenerators[ category ]
							.sort( ( a, b ) => a.order - b.order )
							.map( ( generator, index ) => {
								const IconComponent = generator.icon;
								return (
									<motion.div
										key={ generator.name }
										variants={ cardVariants }
										whileHover="hover"
										className="group"
									>
										<Link
											to={ `/generator/${ generator.route }` }
											className="block h-full"
										>
											<Card className="h-full transition-all duration-300 hover:shadow-lg border-2 hover:border-blue-200 bg-linear-to-br from-white to-gray-50/50">
												<CardHeader className="pb-4">
													<div className="flex items-start justify-between">
														<motion.div
															className="flex items-center justify-center w-12 h-12 bg-linear-to-br from-blue-100 to-purple-100 rounded-xl mb-4"
															whileHover={ { scale: 1.1, rotate: 5 } }
															transition={ { type: 'spring', stiffness: 300 } }
														>
															<IconComponent className="w-6 h-6 text-blue-600" />
														</motion.div>
														{ generator.popular && (
															<motion.div
																initial={ { scale: 0, rotate: -180 } }
																animate={ { scale: 1, rotate: 0 } }
																transition={ {
																	delay: 0.3 + index * 0.1,
																	type: 'spring',
																	stiffness: 200,
																} }
															>
																<Badge
																	variant="secondary"
																	className="bg-linear-to-r from-orange-400 to-red-500 text-white border-0"
																>
																	<Star className="w-3 h-3 mr-1" />
																	{ __( 'Popular', 'easycommerce-fakerpress' ) }
																</Badge>
															</motion.div>
														) }
													</div>
													<CardTitle className="text-xl group-hover:text-blue-700 transition-colors">
														{ generator.name }
													</CardTitle>
												</CardHeader>
												<CardContent className="pt-0">
													<CardDescription className="text-sm leading-relaxed mb-4">
														{ generator.description }
													</CardDescription>
													{ generator.useCase && (
														<div className="pt-4 border-t border-gray-100">
															<p className="text-xs text-gray-500 font-medium flex items-center">
																<span className="inline-block w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
																{ __( 'Best for:', 'easycommerce-fakerpress' ) }{ ' ' }
																{ generator.useCase }
															</p>
														</div>
													) }
													<motion.div
														className="mt-4 flex items-center text-sm text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity"
														initial={ { x: -10 } }
														whileHover={ { x: 0 } }
													>
														{ __( 'Get started', 'easycommerce-fakerpress' ) }
														<ArrowRight className="w-4 h-4 ml-1" />
													</motion.div>
												</CardContent>
											</Card>
										</Link>
									</motion.div>
								);
							} ) }
					</motion.div>
				</motion.div>
			) ) }
		</motion.div>
	);
}

// Generators imported from lib
