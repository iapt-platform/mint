import React from 'react'
import { WikipaliFrame } from './wikipali-frame'
import { IntlProvider, FormattedMessage } from 'react-intl';

const en = require('./translations/en');

const locale = 'en';
const localeMessages = en;

function App() {
  return (
    <IntlProvider locale={locale}
                  messages={localeMessages}>
      <WikipaliFrame>
        <FormattedMessage id="hello-world" />
      </WikipaliFrame>
    </IntlProvider>
  );
}

export default App;
