var List = require("./list");
var fs = require('fs');
// var data = fs.readFileSync("films.txt", 'utf-8');
// console.log(data);

function Customer(name, movie) {
    this.name = name;
    this.movie = movie;
}

function createArr(file) {
    var arr = fs.readFileSync(file, 'utf-8').split("\n");
    for (var i = 0; i < arr.length; ++i) {
        arr[i] = arr[i].trim();
    }
    return arr;
}


function displayList(list) {
    for (list.pos = 0; list.currPos() < list.length() - 1; list.next()) {
        // console.log(list.currPos());
        // console.log(list.getElement());
        if (list.getElement() instanceof Customer) {
            console.log(list.getElement()["name"] + " , " + list.getElement()[
                "movie"]);
        } else {
            console.log(list.getElement());
        }
    }
}

function checkOut(name, movie, filmList, customerList) {
    if (movieList.contains(movie)) {
        var c = new Customer(name, movie);
        customerList.append(c);
        filmList.remove(movie);
    } else {
        console.log(movie + " is not available.");
    }
}

var movies = createArr("films.txt");
var movieList = new List();
var customers = new List();
for (var i = 0; i < movies.length; ++i) {
    movieList.append(movies[i]);
}



console.log("Available movies: \n");
displayList(movieList);
checkOut("Jane Doe", "The Godfather", movieList, customers);
console.log("\nCustomer Rentals: \n");
displayList(customers);



//



//
