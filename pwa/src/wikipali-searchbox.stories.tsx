import React from 'react';
import {WikipaliSearchBox} from './wikipali-searchbox';
import { action } from '@storybook/addon-actions';

export default {
  title: 'Search Box',
  component: WikipaliSearchBox
};

export const NoInput = () => <WikipaliSearchBox />;
