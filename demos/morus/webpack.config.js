const path = require('path');

module.exports = (env, argv) =>{
 return {
    entry: './src/index.js',
    output: {
      path: path.resolve(__dirname, 'dist'),
      filename: argv.mode === 'production' ? 'morus.[contenthash].bundle.js' : 'morus.js',
    },
    target: 'node',
    externals : { canvas: {} }
  };
};
