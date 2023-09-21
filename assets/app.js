/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
require('./sass/_app.scss');

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import $ from 'jquery';
window.jQuery = $;
window.$ = $;
import 'popper.js';
import 'bootstrap';
import 'lazysizes';
import bootbox from 'bootbox';
window.bootbox = bootbox;


require('./js/KimaiWebLoader.js');
global.KimaiPaginatedBoxWidget = require('./js/widgets/KimaiPaginatedBoxWidget').default;
global.KimaiReloadPageWidget = require('./js/widgets/KimaiReloadPageWidget').default;
global.KimaiColor = require('./js/widgets/KimaiColor').default;
global.KimaiStorage = require('./js/widgets/KimaiStorage').default;
