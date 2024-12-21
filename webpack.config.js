const Encore = require('@symfony/webpack-encore');

Encore
  // répertoire de base de ton projet
  .setOutputPath('public/build/')
  .setPublicPath('/build')

  // ajouter la gestion des fichiers Sass et CSS
  .addStyleEntry('styles', './assets/styles/tailwind.css')

  // activer la source map pour le développement
  .enableSourceMaps(!Encore.isProduction())

  // gérer les images et polices avec le loader approprié
  // Webpack Encore gère cela automatiquement sans avoir besoin de enableUrlLoader()

  // activer le CSS et le SCSS si nécessaire
  .enablePostCssLoader()

  // Ajouter cette ligne pour résoudre l'erreur
  .enableSingleRuntimeChunk() // Activation de ce paramètre
;

module.exports = Encore.getWebpackConfig();
