import { defineConfig } from 'umi';

export default defineConfig({
	nodeModulesTransform: {
		type: 'none',
	},
	base: '/my/',
	fastRefresh: {},
	layout: {
		name: 'wikipali',
		locale: true,
		layout: 'side',
	}
});
