var tarjanConnectedComponents =
 require('../../src/graphs/' +
'others/tarjan-connected-components').tarjanConnectedComponents;
var graph = {
    v1: ['v2', 'v5'],
    v2: [],
    v3: ['v1', 'v2', 'v4', 'v5'],
    v4: [],
    v5: []
};
var vertices = topsort(graph); // ['v3', 'v4', 'v1', 'v5', 'v2']