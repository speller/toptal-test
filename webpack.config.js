const path = require('path')
const webpack = require('webpack')
const TerserPlugin = require('terser-webpack-plugin')
// const isDevServer = process.argv.find(v => v.includes('webpack-dev-server'))

// const jsPath = './public/static/js'
const jsDistPath = './public/static/dist'
const entries = {
  'index-page': './src-js/index-page.js',
}


module.exports = (env, argv) => {
  const webpackMode = argv.mode || 'development'
  const appTarget = env.APP_TARGET || 'dev'
  console.log(`Webpack mode: ${webpackMode}`)
  console.log(`Application target: ${appTarget}`)

  // Replace env-related modules depending on the APP_TARGET env variable.
  // Should be used only for conditional config files inclusion.
  const configReplacementPlugin = new webpack.NormalModuleReplacementPlugin(/(.*)-APP_TARGET(\.*)/, function(resource) {
    const r = resource.request.replace(/-APP_TARGET/, `-${appTarget}`)
    console.log(`Replace module '${resource.request}' with '${r}'`)
    resource.request = r
  })

  let config = {
    target: 'web',
    // Dev mode web server config
    devServer: {
      inline: true,
      port: 8084,
      host: '0.0.0.0',
      publicPath: '/static/dist',
      historyApiFallback: true,
      contentBase: __dirname,
      watchContentBase: true,
      disableHostCheck: true,
      watchOptions: {
        ignored: [
          path.resolve(__dirname, 'src'),
          path.resolve(__dirname, 'var'),
          path.resolve(__dirname, 'vendor'),
          path.resolve(__dirname, '.git'),
          path.resolve(__dirname, '.idea'),
        ],
      },
    },
    entry: entries,
    module: {
      rules: [
        {
          test: /\.js$/,
          loaders: ['babel-loader'],
          exclude: /node_modules/,
        },
        {
          test: /\.scss$/,
          use: [
            'style-loader', // Load styles from JS
            'css-loader', // Ability to import CSS in JS module
            'postcss-loader', // Add CSS prefixes
            'sass-loader?sourceMap', // Compile SCSS to CSS
          ],
        },
        {
          test: /\.css$/,
          use: [
            // 'style-loader',
            // MiniCssExtractPlugin.loader,
            'css-loader',
            'postcss-loader',
          ],
        },
        {
          test: /\.(jpe?g|png|gif|svg)$/i,
          loaders: [
            'url-loader',
          ],
        },
        {
          test: /\.(woff|woff2|eot|ttf|svg)$/,
          loader: 'file-loader?name=fonts/[name].[ext]',
        },
      ],
    },
    optimization: {
    },
    // Where to put compiled JS
    output: {
      path: path.resolve(__dirname, jsDistPath),
      filename: '[name].js',
    },
    plugins: [
      configReplacementPlugin,
    ],
  }
  if (webpackMode === 'production') {
    // Add JS uglifier and CSS optimization in prod mode
    config.optimization.minimizer = [
      new TerserPlugin(),
    ]
  }

  return config
}

// module.exports = {
//     context: __dirname,
//     entry: {
//         bundle: './public/static/js/entry.js',
//         offer: './public/static/js/page/offer.js',
//     },
//     output: {
//         filename: '[name].js',
//         path: path.resolve(__dirname, jsDistPath),
//     },
//     resolve: {
//         alias: {
//             handlebars: 'handlebars/dist/handlebars.min.js',
//         },
//     },
//     module: {
//         rules: [
//             {
//                 test: /\.js?$/,
//                 include: [path.resolve(__dirname, jsPath)],
//                 loader: 'babel-loader',
//             },
//         ],
//     },
// };
