const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

var colors = require('colors');

let entry = {
  output: {
    path: path.resolve(__dirname, '../Resources/Public/JavaScript'),
    filename: '[name].js',
    library: '[name]',
  },

  devtool: false,
  plugins: [
    new webpack.ProgressPlugin({
      activeModules: false,
      entries: true,
      handler(percentage, message, ...args) {
        if (percentage == 1) {
          console.log(colors.cyan.underline("finished at " + new Date()));
        }
      },
      modules: true,
      modulesCount: 5000,
      profile: false,
      dependencies: true,
      dependenciesCount: 10000,
      percentBy: null
    }),
    new BrowserSyncPlugin({
      host: 'clubmanagert11.ddev.site',
      port: 3000,
      proxy: 'https://clubmanagert11.ddev.site/',
      files: ['../**/*.html', '../**/*.php', './**/*.typoscript', '../Resources/Public/*', '../Resources/Public/*']
    }),
    new MiniCssExtractPlugin({
      filename: "../Css/[name].css", // change this RELATIVE to your output.path!
      chunkFilename: "[id].css",
    }),
    new webpack.SourceMapDevToolPlugin({
      filename: '[file].map[query]'
    })
  ],
  stats: {
    colors: true,
    hash: false,
    version: false,
    timings: true,
    assets: false,
    chunks: false,
    modules: false,
    reasons: false,
    children: false,
    source: false,
    errors: true,
    errorDetails: true,
    warnings: true,
    publicPath: false
  },
  resolve: {
    extensions: ['.js', '.jsx', '.json', '.ts', '.tsx'],
    modules: [path.join(__dirname, 'node_modules'), 'node_modules'],
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },/*
      {
        test: /\.css$/i,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader", 
            options: {
              url: true,
            }
          }
        ],
      },*/
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              url: true,
            }
          },

          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [
                  [
                    "postcss-preset-env",
                    {
                      // Options
                    },
                  ],
                ],
              },
            }
          },
          {
            loader: 'sass-loader',
          }
        ]
      },
      {
        test: /\.(svg|eot|woff|woff2|ttf)$/,
        type: 'asset/resource',
        generator: {
          filename: '../GeneratedResources/[name][ext]'
        }
      },

      {
        test: /.(jpg|jpeg|png)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              outputPath: '../GeneratedResources',
              publicPath: '../GeneratedResources',

              name(resourcePath, resourceQuery) {
                return '[path][name].[ext]';
              }

            }
          }
        ]
      }
    ]
  },
  optimization: {
    minimizer: [
      // For webpack@5 you can use the `...` syntax to extend existing minimizers (i.e. `terser-webpack-plugin`), uncomment the next line
      // `...`,
      new CssMinimizerPlugin(),
    ],
  },
};

let clubmanagerModules = { ...entry };

module.exports = function (env, args) {
  clubmanagerModules.entry = {
    Main: [path.resolve(__dirname, '../Resources/Private/JavaScript/Main.js'), path.resolve(__dirname, '../Resources/Private/Scss/main.scss')],
  };
  clubmanagerModules.output.path = path.resolve(__dirname, '../Resources/Public/JavaScript/');
  clubmanagerModules.externals = {};
  return [clubmanagerModules];
};
