import React from 'react';
import {
	BasicLayoutProps,
	Settings as LayoutSettings,
} from '@ant-design/pro-layout';
export const layout = ({
	initialState,
}: {
	initialState: { settings?: LayoutSettings; currentUser?: API.CurrentUser };
}): BasicLayoutProps => {
	return {
		rightContentRender: () => <RightContent />,
		footerRender: () => <Footer />,
		onPageChange: () => {
			const { currentUser } = initialState;
			const { location } = history;
			if (!currentUser && location.pathname != '/user/login') {
				history.push('/user/login');
			}
		},
		menuHeaderRender: undefined,
		...initialState?.settings,
	};
};