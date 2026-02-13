#!/bin/bash

set -e

npm install --save \
    react-router react-intl @reduxjs/toolkit react-redux \
    @graphiql/react \
    usehooks-ts jose \
    js-cookie @types/js-cookie \
    antd @ant-design/x \
    rc-virtual-list
# FIXME
npm install --save @ant-design/pro-components@beta

npm ls

exit 0
