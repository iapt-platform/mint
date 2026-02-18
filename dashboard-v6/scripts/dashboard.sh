#!/bin/bash

set -e

npm install --save \
    react-router react-intl @reduxjs/toolkit react-redux \
    @graphiql/react usehooks-ts rc-virtual-list \
    jose dayjs \
    remark-gfm react-markdown @uiw/react-md-editor @mdxeditor/editor \
    slate slate-history slate-react \
    lodash @types/lodash js-cookie @types/js-cookie diff @types/diff marked @types/marked video.js @types/video.js \
    antd @ant-design/x @ant-design/charts @ant-design/charts @ant-design/pro-components@beta

npm ls

exit 0
