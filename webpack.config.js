const Encore = require('@symfony/webpack-encore');
const webpack = require('webpack');

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('app', './assets/react/main.tsx')
  .enableStimulusBridge('./assets/controllers.json')
  .enableReactPreset()
  .enableTypeScriptLoader()
  .enableSingleRuntimeChunk()
  .addPlugin(new webpack.DefinePlugin({
    'process.env.OAUTH2_AUTHORITY': JSON.stringify(process.env.OAUTH2_AUTHORITY || ''),
    'process.env.OAUTH2_CLIENT_ID': JSON.stringify(process.env.OAUTH2_CLIENT_ID || ''),
    'process.env.OAUTH2_REDIRECT_URI': JSON.stringify(process.env.OAUTH2_REDIRECT_URI || ''),
    'process.env.OAUTH2_POST_LOGOUT_REDIRECT_URI': JSON.stringify(process.env.OAUTH2_POST_LOGOUT_REDIRECT_URI || ''),
    'process.env.OAUTH2_SCOPE': JSON.stringify(process.env.OAUTH2_SCOPE || ''),
  }))
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction());

module.exports = Encore.getWebpackConfig();
