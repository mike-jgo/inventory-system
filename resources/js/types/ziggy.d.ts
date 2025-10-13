declare module 'ziggy-js' {
	import { Config, RouteParamsWithQueryOverload } from 'ziggy-js';
	export function route(
		name?: string,
		params?: RouteParamsWithQueryOverload,
		absolute?: boolean,
		config?: Config
	): string;
}
