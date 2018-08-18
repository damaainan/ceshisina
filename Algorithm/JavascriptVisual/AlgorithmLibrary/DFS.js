// Copyright 2011 David Galles, University of San Francisco. All rights reserved.
//
// Redistribution and use in source and binary forms, with or without modification, are
// permitted provided that the following conditions are met:
//
// 1. Redistributions of source code must retain the above copyright notice, this list of
// conditions and the following disclaimer.
//
// 2. Redistributions in binary form must reproduce the above copyright notice, this list
// of conditions and the following disclaimer in the documentation and/or other materials
// provided with the distribution.
//
// THIS SOFTWARE IS PROVIDED BY <COPYRIGHT HOLDER> ``AS IS'' AND ANY EXPRESS OR IMPLIED
// WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
// FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> OR
// CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
// CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
// ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
// NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
// ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
// The views and conclusions contained in the software and documentation are those of the
// authors and should not be interpreted as representing official policies, either expressed
// or implied, of the University of San Francisco


var AUX_ARRAY_WIDTH = 25;
var AUX_ARRAY_HEIGHT = 25;
var AUX_ARRAY_START_Y = 50;

var VISITED_START_X = 475;
var PARENT_START_X = 400;

var HIGHLIGHT_CIRCLE_COLOR = "#000000";
var DFS_TREE_COLOR = "#0000FF";
var BFS_QUEUE_HEAD_COLOR = "#0000FF";


var QUEUE_START_X = 30;
var QUEUE_START_Y = 50;
var QUEUE_SPACING = 30;


function DFS(am)
{
	this.init(am);
	
}

DFS.prototype = new Graph();
DFS.prototype.constructor = DFS;
DFS.superclass = Graph.prototype;

DFS.prototype.addControls =  function()
{		
	addLabelToAlgorithmBar("Start Vertex: ");
	this.startField = addControlToAlgorithmBar("Text", "");
	this.startField.onkeydown = this.returnSubmit(this.startField,  this.startCallback.bind(this), 2, true);
	this.startButton = addControlToAlgorithmBar("Button", "Run DFS");
	this.startButton.onclick = this.startCallback.bind(this);
	DFS.superclass.addControls.call(this);
}	


DFS.prototype.init = function(am, w, h)
{
	showEdgeCosts = false;
	DFS.superclass.init.call(this, am, w, h); // TODO:  add no edge label flag to this?
	// Setup called in base class constructor
}


DFS.prototype.setup = function() 
{
	DFS.superclass.setup.call(this);
	this.messageID = new Array();
	this.commands = new Array();
	this.visitedID = new Array(this.size);
	this.visitedIndexID = new Array(this.size);
	this.parentID = new Array(this.size);
	this.parentIndexID = new Array(this.size);
	for (var i = 0; i < this.size; i++)
	{
		this.visitedID[i] = this.nextIndex++;
		this.visitedIndexID[i] = this.nextIndex++;
		this.parentID[i] = this.nextIndex++;
		this.parentIndexID[i] = this.nextIndex++;
		this.cmd("CreateRectangle", this.visitedID[i], "f", AUX_ARRAY_WIDTH, AUX_ARRAY_HEIGHT, VISITED_START_X, AUX_ARRAY_START_Y + i*AUX_ARRAY_HEIGHT);
		this.cmd("CreateLabel", this.visitedIndexID[i], i, VISITED_START_X - AUX_ARRAY_WIDTH , AUX_ARRAY_START_Y + i*AUX_ARRAY_HEIGHT);
		this.cmd("SetForegroundColor",  this.visitedIndexID[i], VERTEX_INDEX_COLOR);
		this.cmd("CreateRectangle", this.parentID[i], "", AUX_ARRAY_WIDTH, AUX_ARRAY_HEIGHT, PARENT_START_X, AUX_ARRAY_START_Y + i*AUX_ARRAY_HEIGHT);
		this.cmd("CreateLabel", this.parentIndexID[i], i, PARENT_START_X - AUX_ARRAY_WIDTH , AUX_ARRAY_START_Y + i*AUX_ARRAY_HEIGHT);
		this.cmd("SetForegroundColor",  this.parentIndexID[i], VERTEX_INDEX_COLOR);
		
	}
	this.cmd("CreateLabel", this.nextIndex++, "Parent", PARENT_START_X - AUX_ARRAY_WIDTH, AUX_ARRAY_START_Y - AUX_ARRAY_HEIGHT * 1.5, 0);
	this.cmd("CreateLabel", this.nextIndex++, "Visited", VISITED_START_X - AUX_ARRAY_WIDTH, AUX_ARRAY_START_Y - AUX_ARRAY_HEIGHT * 1.5, 0);
	this.animationManager.setAllLayers([0, this.currentLayer]);
	this.animationManager.StartNewAnimation(this.commands);
	this.animationManager.skipForward();
	this.animationManager.clearHistory();
	this.highlightCircleL = this.nextIndex++;
	this.highlightCircleAL = this.nextIndex++;
	this.highlightCircleAM= this.nextIndex++
}

DFS.prototype.startCallback = function(event)
{
	var startValue;
	
	if (this.startField.value != "")
	{
		startvalue = this.startField.value;
		this.startField.value = "";
		if (parseInt(startvalue) < this.size)
			this.implementAction(this.doDFS.bind(this),startvalue);
	}
}



DFS.prototype.doDFS = function(startVetex)
{
	this.visited = new Array(this.size);
	this.commands = new Array();
	if (this.messageID != null)
	{
		for (var i = 0; i < this.messageID.length; i++)
		{
			this.cmd("Delete", this.messageID[i]);
		}
	}
	this.rebuildEdges();
	this.messageID = new Array();
	for (i = 0; i < this.size; i++)
	{
		this.cmd("SetText", this.visitedID[i], "f");
		this.cmd("SetText", this.parentID[i], "");
		this.visited[i] = false;
	}
	var vertex = parseInt(startVetex);
	this.cmd("CreateHighlightCircle", this.highlightCircleL, HIGHLIGHT_CIRCLE_COLOR, this.x_pos_logical[vertex], this.y_pos_logical[vertex]);
	this.cmd("SetLayer", this.highlightCircleL, 1);
	this.cmd("CreateHighlightCircle", this.highlightCircleAL, HIGHLIGHT_CIRCLE_COLOR,this.adj_list_x_start - this.adj_list_width, this.adj_list_y_start + vertex*this.adj_list_height);
	this.cmd("SetLayer", this.highlightCircleAL, 2);
	
	this.cmd("CreateHighlightCircle", this.highlightCircleAM, HIGHLIGHT_CIRCLE_COLOR,this.adj_matrix_x_start  - this.adj_matrix_width, this.adj_matrix_y_start + vertex*this.adj_matrix_height);
	this.cmd("SetLayer", this.highlightCircleAM, 3);
	
	this.messageY = 30;
	this.dfsVisit(vertex, 10);
	this.cmd("Delete", this.highlightCircleL);
	this.cmd("Delete", this.highlightCircleAL);
	this.cmd("Delete", this.highlightCircleAM);
	return this.commands
	
}


DFS.prototype.dfsVisit = function(startVertex, messageX)
{
	var nextMessage = this.nextIndex++;
	this.messageID.push(nextMessage);
	
	this.cmd("CreateLabel",nextMessage, "DFS(" +  String(startVertex) +  ")", messageX, this.messageY, 0);
	this.messageY = this.messageY + 20;
	if (!this.visited[startVertex])
	{
		this.visited[startVertex] = true;
		this.cmd("SetText", this.visitedID[startVertex], "T");
		this.cmd("Step");
		for (var neighbor = 0; neighbor < this.size; neighbor++)
		{
			if (this.adj_matrix[startVertex][neighbor] > 0)
			{
				this.highlightEdge(startVertex, neighbor, 1);
				this.cmd("SetHighlight", this.visitedID[neighbor], 1);
				if (this.visited[neighbor])
				{
					nextMessage = this.nextIndex;
					this.cmd("CreateLabel", nextMessage, "Vertex " + String(neighbor) + " already visited.", messageX, this.messageY, 0);
				}
				this.cmd("Step");
				this.highlightEdge(startVertex, neighbor, 0);
				this.cmd("SetHighlight", this.visitedID[neighbor], 0);
				if (this.visited[neighbor])
				{
					this.cmd("Delete", nextMessage);
				}
				
				if (!this.visited[neighbor])
				{
					this.cmd("Disconnect", this.circleID[startVertex], this.circleID[neighbor]);
					this.cmd("Connect", this.circleID[startVertex], this.circleID[neighbor], DFS_TREE_COLOR, this.curve[startVertex][neighbor], 1, "");
					this.cmd("Move", this.highlightCircleL, this.x_pos_logical[neighbor], this.y_pos_logical[neighbor]);
					this.cmd("Move", this.highlightCircleAL, this.adj_list_x_start - this.adj_list_width, this.adj_list_y_start + neighbor*this.adj_list_height);
					this.cmd("Move", this.highlightCircleAM, this.adj_matrix_x_start - this.adj_matrix_width, this.adj_matrix_y_start + neighbor*this.adj_matrix_height);
					
					this.cmd("SetText", this.parentID[neighbor], startVertex);
					this.cmd("Step");
					this.dfsVisit(neighbor, messageX + 20);							
					nextMessage = this.nextIndex;
					this.cmd("CreateLabel", nextMessage, "Returning from recursive call: DFS(" + String(neighbor) + ")", messageX + 20, this.messageY, 0);
					
					this.cmd("Move", this.highlightCircleAL, this.adj_list_x_start - this.adj_list_width, this.adj_list_y_start + startVertex*this.adj_list_height);
					this.cmd("Move", this.highlightCircleL, this.x_pos_logical[startVertex], this.y_pos_logical[startVertex]);
					this.cmd("Move", this.highlightCircleAM, this.adj_matrix_x_start - this.adj_matrix_width, this.adj_matrix_y_start + startVertex*this.adj_matrix_height);
					this.cmd("Step");
					this.cmd("Delete", nextMessage);
				}
				this.cmd("Step");
				
				
				
			}
			
		}
		
	}
	
}



// NEED TO OVERRIDE IN PARENT
DFS.prototype.reset = function()
{
	// Throw an error?
}




DFS.prototype.enableUI = function(event)
{			
	this.startField.disabled = false;
	this.startButton.disabled = false;
	this.startButton
	
	
	DFS.superclass.enableUI.call(this,event);
}
DFS.prototype.disableUI = function(event)
{
	
	this.startField.disabled = true;
	this.startButton.disabled = true;
	
	DFS.superclass.disableUI.call(this, event);
}


var currentAlg;

function init()
{
	var animManag = initCanvas();
	currentAlg = new DFS(animManag, canvas.width, canvas.height);
}
