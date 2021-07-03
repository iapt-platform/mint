import { defineConfig } from 'umi';

export default defineConfig({
	nodeModulesTransform: {
		type: 'none',
	},
	base: '/my/',
	fastRefresh: {},
	routes: [
		{ exact: true, path: "/", component: 'index' },
		{ exact: true, path: "/items", component: 'items' },
		{ exact: true, path: "/course", component: 'course' },
	],
	layout: {
		name: 'wikipali',
		locale: true,
		layout: 'side',
	}
});
