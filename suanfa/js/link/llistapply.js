var LList = require("./linkedlist");

// var cities = new LList();
// cities.insert("Conway", "head");
// cities.insert("Russellville", "Conway");
// cities.insert("Alma", "Russellville");
// cities.display();


var cities = new LList();
cities.insert("Conway", "head");
cities.insert("Russellville", "Conway");
cities.insert("Carlisle", "Russellville");
cities.insert("Alma", "Carlisle");
cities.display();
cities.remove("Carlisle");
cities.display();
