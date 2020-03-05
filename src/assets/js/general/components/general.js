const general = {
	init: () => {
		//fetch('http://127.0.0.1/wp-tests/wp-content/debug.log')
		fetch("C:/xampp/htdocs/wp-tests/wp-content/debug.log")
			.then(response => response.text())
			.then((data) => {
				const dataArr = data.split('\n');
				const dataArrWithoutDate = dataArr.map(function (item) {
					item = item.replace(/^\[.+\]\s/i, '');
					return item;
				});
				const dataArrWithoutDateUnique = dataArrWithoutDate.filter((value, index, self) => self.indexOf(value) === index);
				const dataArrUnique = dataArrWithoutDateUnique.map((item, index) => {
					return dataArrWithoutDate.lastIndexOf(item);
				});
				console.log('=== debug.log ===');
				dataArrUnique.map((item) => {
					console.log(dataArr[item]);
					return item;
				})
			});
	}
}

module.exports = general;

// Required in our shared function.
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
