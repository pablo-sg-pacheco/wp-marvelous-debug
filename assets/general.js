/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/assets/js/general/components/general.js":
/*!*****************************************************!*\
  !*** ./src/assets/js/general/components/general.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var general = {
  init: function init() {
    //fetch('http://127.0.0.1/wp-tests/wp-content/debug.log')
    fetch("C:/xampp/htdocs/wp-tests/wp-content/debug.log").then(function (response) {
      return response.text();
    }).then(function (data) {
      var dataArr = data.split('\n');
      var dataArrWithoutDate = dataArr.map(function (item) {
        item = item.replace(/^\[.+\]\s/i, '');
        return item;
      });
      var dataArrWithoutDateUnique = dataArrWithoutDate.filter(function (value, index, self) {
        return self.indexOf(value) === index;
      });
      var dataArrUnique = dataArrWithoutDateUnique.map(function (item, index) {
        return dataArrWithoutDate.lastIndexOf(item);
      });
      console.log('=== debug.log ===');
      dataArrUnique.map(function (item) {
        console.log(dataArr[item]);
        return item;
      });
    });
  }
};
module.exports = general; // Required in our shared function.
//const { upper, isString } = require( '../../utils/utils-index' );
// Require in the last function from Lodash.
//const { last } = require('lodash');

/*const front = {
	log( message ) {
		if ( isString( message ) ) {
			console.log( upper( message ) );
		} else {
			console.log( message );
		}
	},
	abc: ( message ) => console.log( message ),
};

front.abc( '678' );

module.exports = front;*/

/***/ }),

/***/ "./src/assets/js/general/general-index.js":
/*!************************************************!*\
  !*** ./src/assets/js/general/general-index.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var general = __webpack_require__(/*! ./components/general */ "./src/assets/js/general/components/general.js");

general.init(); //front.log( 'Here is a message for the frontend!' );
//console.log( 'asas2' );
// Let's test a function using Lodash.
//front.log( front.getLastArrayElement( [ 1, 2, 3 ] ) ); // Should log out 3.

/***/ }),

/***/ 0:
/*!******************************************************!*\
  !*** multi ./src/assets/js/general/general-index.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./src/assets/js/general/general-index.js */"./src/assets/js/general/general-index.js");


/***/ })

/******/ });
//# sourceMappingURL=general.js.map