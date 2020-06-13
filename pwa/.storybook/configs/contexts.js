import { IntlProvider } from 'react-intl';

const en = require('../../src/translations/en')
const zhCN = require('../../src/translations/zh-CN')

export const contexts = [
    {
        icon: 'globe',
        title: 'i18n',
        components: [
            IntlProvider
        ],
        params: [
            { name: 'en', props: { locale: 'en', messages: en }},
            { name: 'zh-CN', props: { locale: 'zh-CN', messages: zhCN }},
        ]
    },
  ];
  