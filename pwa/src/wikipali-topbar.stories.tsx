import React from 'react';
import {WikipaliTopbar} from './wikipali-topbar';
import { action } from '@storybook/addon-actions';

export default {
  title: 'Main Topbar',
  component: WikipaliTopbar,
};

export const LoggedIn = () => <WikipaliTopbar userLoggedIn={true} />;
export const LoggedOut = () => <WikipaliTopbar userLoggedIn={false} />;