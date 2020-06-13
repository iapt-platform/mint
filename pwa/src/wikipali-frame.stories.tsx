import React from 'react';
import { WikipaliFrame } from './wikipali-frame';
import { FormattedMessage } from 'react-intl';

export default {
  title: 'Frame',
  component: WikipaliFrame,
};

export const Basic = () => (
    <WikipaliFrame>
        <FormattedMessage id="hello-world" />
    </WikipaliFrame>
)