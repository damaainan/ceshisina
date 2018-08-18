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



var FIRST_PRINT_POS_X = 50;
var PRINT_VERTICAL_GAP = 20;
var PRINT_MAX = 990;
var PRINT_HORIZONTAL_GAP = 50;

var MIN_MAX_DEGREE = 3;
var MAX_MAX_DEGREE = 7;

var HEIGHT_DELTA  = 50;
var NODE_SPACING = 3; 
var STARTING_Y = 30;
var WIDTH_PER_ELEM = 40;
var NODE_HEIGHT = 20;

var MESSAGE_X = 5;
var MESSAGE_Y = 10;

var LINK_COLOR = "#007700";
var HIGHLIGHT_CIRCLE_COLOR = "#007700";
var FOREGROUND_COLOR = "#007700";
var BACKGROUND_COLOR = "#EEFFEE";
var PRINT_COLOR = FOREGROUND_COLOR;



function BTree(am, w, h)
{
	this.init(am, w, h);

}

BTree.prototype = new Algorithm();
BTree.prototype.varructor = BTree;
BTree.superclass = Algorithm.prototype;





BTree.prototype.init = function(am, w, h)
{
	BTree.superclass.init.call(this, am, w, h);
	this.nextIndex = 0;

	this.starting_x = w / 2;

	this.preemptiveSplit = false
	
	
	this.addControls();
	
	
	this.max_keys = 2;
	this.min_keys = 1;
	this.split_index = 1;
	
	this.max_degree = 3;
	
	
	
	
	this.messageID = this.nextIndex++;
	this.cmd("CreateLabel", this.messageID, "", MESSAGE_X, MESSAGE_Y, 0);
	this.moveLabel1ID = this.nextIndex++;
	this.moveLabel2ID = this.nextIndex++;
	
	animationManager.StartNewAnimation(this.commands);
	animationManager.skipForward();
	animationManager.clearHistory();
	this.commands = new Array();
	
	this.first_print_pos_y = h - 3 * PRINT_VERTICAL_GAP;

	
	this.xPosOfNextLabel = 100;
	this.yPosOfNextLabel = 200;
}

BTree.prototype.addControls =  function()
{
	this.controls = [];
	
	this.insertField = addControlToAlgorithmBar("Text", "");
	this.insertField.onkeydown = this.returnSubmit(this.insertField,  this.insertCallback.bind(this), 4);
	this.controls.push(this.insertField);
	
	this.insertButton = addControlToAlgorithmBar("Button", "Insert");
	this.insertButton.onclick = this.insertCallback.bind(this);
	this.controls.push(this.insertButton);
	
	this.deleteField = addControlToAlgorithmBar("Text", "");
	this.deleteField.onkeydown = this.returnSubmit(this.deleteField,  this.deleteCallback.bind(this), 4);
	this.controls.push(this.deleteField);
	
	this.deleteButton = addControlToAlgorithmBar("Button", "Delete");
	this.deleteButton.onclick = this.deleteCallback.bind(this);
	this.controls.push(this.deleteButton);
	
	this.findField = addControlToAlgorithmBar("Text", "");
	this.findField.onkeydown = this.returnSubmit(this.findField,  this.findCallback.bind(this), 4);
	this.controls.push(this.findField);
	
	this.findButton = addControlToAlgorithmBar("Button", "Find");
	this.findButton.onclick = this.findCallback.bind(this);
	this.controls.push(this.findButton);
	
	this.printButton = addControlToAlgorithmBar("Button", "Print");
	this.printButton.onclick = this.printCallback.bind(this);
	this.controls.push(this.printButton);
	
	this.clearButton = addControlToAlgorithmBar("Button", "Clear");
	this.clearButton.onclick = this.clearCallback.bind(this);
	this.controls.push(this.clearButton);
	
	var i;
	radioButtonNames = [];
	for (i = MIN_MAX_DEGREE; i <= MAX_MAX_DEGREE; i++)
	{
		radioButtonNames.push("Max. Degree = " + String(i));
	}
	
	this.maxDegreeRadioButtons = addRadioButtonGroupToAlgorithmBar(radioButtonNames, "MaxDegree");
	
	this.maxDegreeRadioButtons[0].checked = true;
	for(i = 0; i < this.maxDegreeRadioButtons.length; i++)
	{
		this.maxDegreeRadioButtons[i].onclick = this.maxDegreeChangedHandler.bind(this,i+MIN_MAX_DEGREE);
	}
	
	
	this.premptiveSplitBox = addCheckboxToAlgorithmBar("Preemtive Split / Merge (Even max degree only)");
	this.premptiveSplitBox.onclick = this.premtiveSplitCallback.bind(this);
	
	
	// Other buttons ...
	
}


		
		
				
BTree.prototype.reset = function()
{
	this.nextIndex = 3;
	this.max_degree = 3;
	this.max_keys = 2;
	this.min_keys = 1;
	this.split_index = 1;
	// NOTE: The order of these last two this.commands matters!
	this.treeRoot = null;
	this.ignoreInputs = true;
	// maxDegreeButtonArray[this.max_degree].selected = true;
	this.ignoreInputs = false;
}

		
BTree.prototype.enableUI = function(event)
{
	var i;
	for (i = 0; i < this.controls.length; i++)
	{
		this.controls[i].disabled = false;
	}
	
	// TODO  Only enable even maxdegree if preemptive merge is on
	
	if (this.preemptiveSplit)
	{
		var initialEven = MIN_MAX_DEGREE % 2;
		var i;
		for (i = initialEven; i <= MAX_MAX_DEGREE - MIN_MAX_DEGREE; i+= 2)
		{
			this.maxDegreeRadioButtons[i].disabled = false;
		}
	}
	else
	{
		for (i = 0; i < this.maxDegreeRadioButtons.length; i++)
		{	
			this.maxDegreeRadioButtons[i].disabled = false;
		}
	}
	
	
	
	
	
	if (this.max_degree % 2 == 0)
	{
		this.premptiveSplitBox.disabled = false;
	}
	
	
}
BTree.prototype.disableUI = function(event)
{
	for (var i = 0; i < this.controls.length; i++)
	{
		this.controls[i].disabled = true;
	}

	for (i = 0; i < this.maxDegreeRadioButtons.length; i++)
	{	
		this.maxDegreeRadioButtons[i].disabled = true;
	}
	
	this.premptiveSplitBox.disabled = true;
	
	
	
}


//TODO:  Fix me!
BTree.prototype.maxDegreeChangedHandler = function(newMaxDegree, event) 
{
	if (this.max_degree != newMaxDegree)
	{
		this.implementAction(this.changeDegree.bind(this), newMaxDegree);
        	animationManager.skipForward();
    	        animationManager.clearHistory();


	}
}
		


BTree.prototype.insertCallback = function(event)
{
	var insertedValue;
	insertedValue = this.normalizeNumber(this.insertField.value, 4);
	if (insertedValue != "")
	{
		this.insertField.value = "";
		this.implementAction(this.insertElement.bind(this),insertedValue);
	}
}
		
BTree.prototype.deleteCallback = function(event)
{
	var deletedValue = this.deleteField.value;
	if (deletedValue != "")
	{
		deletedValue = this.normalizeNumber(this.deleteField.value, 4);
		this.deleteField.value = "";
		this.implementAction(this.deleteElement.bind(this),deletedValue);		
	}
}
		
BTree.prototype.clearCallback = function(event)
{
	this.implementAction(this.clearTree.bind(this), "");
}
		
		
BTree.prototype.premtiveSplitCallback = function(event)
{
	if (this.preemptiveSplit != this.premptiveSplitBox.checked)
	{
		this.implementAction(this.changePreemtiveSplit.bind(this), this.premptiveSplitBox.checked);
	}
}

		
BTree.prototype.changePreemtiveSplit = function(newValue)
{
	this.commands = new Array();
	this.cmd("Step");
	this.preemptiveSplit = newValue;
	if (this.premptiveSplitBox.checked != this.preemptiveSplit)
	{
		this.premptiveSplitBox.checked = this.preemptiveSplit;
	}
	return this.commands;			
}
		

BTree.prototype.printCallback = function(event) 
{
	this.implementAction(this.printTree.bind(this),"");						
}

BTree.prototype.printTree = function(unused)
{
	this.commands = new Array();
	this.cmd("SetText", this.messageID, "Printing tree");
	var firstLabel = this.nextIndex;
	
	this.xPosOfNextLabel = FIRST_PRINT_POS_X;
	this.yPosOfNextLabel = this.first_print_pos_y;
	
	this.printTreeRec(this.treeRoot);
	this.cmd("Step");
	for (var i = firstLabel; i < this.nextIndex; i++)
	{
		this.cmd("Delete", i);
	}
	this.nextIndex = firstLabel;
	this.cmd("SetText", this.messageID, "");
	return this.commands;
}
		
BTree.prototype.printTreeRec =function (tree)
{
	this.cmd("SetHighlight", tree.graphicID, 1);
	var nextLabelID;
	if (tree.isLeaf)
	{
		for (var i = 0; i < tree.numKeys;i++)
		{
			nextLabelID = this.nextIndex++;
			this.cmd("CreateLabel", nextLabelID, tree.keys[i], this.getLabelX(tree, i), tree.y);
			this.cmd("SetForegroundColor", nextLabelID, PRINT_COLOR);
			this.cmd("Move", nextLabelID, this.xPosOfNextLabel, this.yPosOfNextLabel);
			this.cmd("Step");			
			this.xPosOfNextLabel +=  PRINT_HORIZONTAL_GAP;
			if (this.xPosOfNextLabel > PRINT_MAX)
			{
				this.xPosOfNextLabel = FIRST_PRINT_POS_X;
				this.yPosOfNextLabel += PRINT_VERTICAL_GAP;
			}
		}
		this.cmd("SetHighlight", tree.graphicID, 0);
	}
	else
	{
		this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[0].graphicID, 1);
		this.cmd("Step");
		this.cmd("SetHighlight", tree.graphicID, 0);
		this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[0].graphicID, 0);
		this.printTreeRec(tree.children[0]);
		for (i = 0; i < tree.numKeys; i++)
		{
			this.cmd("SetHighlight", tree.graphicID, 1);
			nextLabelID = this.nextIndex++;
			this.cmd("CreateLabel", nextLabelID, tree.keys[i], this.getLabelX(tree, i), tree.y);
			this.cmd("SetForegroundColor", nextLabelID, PRINT_COLOR);
			this.cmd("Move", nextLabelID, this.xPosOfNextLabel, this.yPosOfNextLabel);
			this.cmd("Step");			
			this.xPosOfNextLabel +=  PRINT_HORIZONTAL_GAP;
			if (this.xPosOfNextLabel > PRINT_MAX)
			{
				this.xPosOfNextLabel = FIRST_PRINT_POS_X;
				this.yPosOfNextLabel += PRINT_VERTICAL_GAP;
			}
			this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i+1].graphicID, 1);
			this.cmd("Step");
			this.cmd("SetHighlight", tree.graphicID, 0);
			this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i+1].graphicID, 0);
			this.printTreeRec(tree.children[i+1]);
		}
		this.cmd("SetHighlight", tree.graphicID, 1);
		this.cmd("Step");
		this.cmd("SetHighlight", tree.graphicID, 0);
		
	}
	
	
}

BTree.prototype.clearTree = function(ignored)
{
	this.commands = new Array();
	this.deleteTree(this.treeRoot);
	this.treeRoot = null;
	this.nextIndex = 3;		
	return this.commands;
}

BTree.prototype.deleteTree = function(tree)
{
	if (tree != null)
	{
		if (!tree.isLeaf)
		{
			for (var i = 0; i <= tree.numKeys; i++)
			{
				this.cmd("Disconnect", tree.graphicID, tree.children[i].graphicID);
				this.deleteTree(tree.children[i]);
				tree.children[i] == null;
			}
		}
		this.cmd("Delete", tree.graphicID);
	}
}


BTree.prototype.changeDegree = function(degree)
{
	this.commands = new Array();
	this.deleteTree(this.treeRoot);
	this.treeRoot = null;
	this.nextIndex = 3;
	var newDegree = degree;
	this.ignoreInputs = true;
	//TODO:  Check me!
	this.maxDegreeRadioButtons[newDegree - MIN_MAX_DEGREE].checked = true;
	
	this.ignoreInputs = false;
	this.max_degree = newDegree;
	this.max_keys = newDegree - 1;
	this.min_keys = Math.floor((newDegree + 1) / 2) - 1;
	this.split_index = Math.floor((newDegree - 1) / 2);
	if (this.commands.length == 0)
	{
		this.cmd("Step");
	}
	if (newDegree % 2 != 0 && this.preemptiveSplit)
	{
		this.preemptiveSplit = false;
		this.premptiveSplitBox.checked = false;
	}
	return this.commands;
}


BTree.prototype.findCallback = function(event)
{
	var findValue;
	findValue = this.normalizeNumber(this.findField.value, 4);
	this.findField.value = "";
	this.implementAction(this.findElement.bind(this),findValue);						
}

BTree.prototype.findElement = function(findValue)
{
	this.commands = new Array();
	
	this.cmd("SetText", this.messageID, "Finding " + findValue);
	this.findInTree(this.treeRoot, findValue);
	
	return this.commands;
}

BTree.prototype.findInTree = function(tree, val)
{
	if (tree != null)
	{
		this.cmd("SetHighlight", tree.graphicID, 1);
		this.cmd("Step");
		var i;
		for (i = 0; i < tree.numKeys && tree.keys[i] < val; i++);
		if (i == tree.numKeys)
		{
			if (!tree.isLeaf)
			{
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[tree.numKeys].graphicID, 1);
				this.cmd("Step");
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[tree.numKeys].graphicID, 0);
				this.findInTree(tree.children[tree.numKeys], val);
			}
			else
			{
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetText", this.messageID, "Element " + val + " is not in the tree");
			}
		}
		else if (tree.keys[i] > val)
		{
			if (!tree.isLeaf)
			{
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 1);
				this.cmd("Step");
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 0);					
				this.findInTree(tree.children[i], val);
			}
			else
			{
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetText", this.messageID, "Element " + val + " is not in the tree");
			}
		}
		else
		{
			this.cmd("SetTextColor", tree.graphicID, "#FF0000", i);
			this.cmd("SetText", this.messageID, "Element " + val + " found");
			this.cmd("Step");
			this.cmd("SetTextColor", tree.graphicID, FOREGROUND_COLOR, i);
			this.cmd("SetHighlight", tree.graphicID, 0);
			
			this.cmd("Step");
		}
	}
	else
	{
		this.cmd("SetText", this.messageID, "Element " + val + " is not in the tree");
	}
}


BTree.prototype.insertElement = function(insertedValue)
{
	this.commands = new Array();
	
	this.cmd("SetText", this.messageID, "Inserting " + insertedValue);
	this.cmd("Step");
	
	if (this.treeRoot == null)
	{
		this.treeRoot = new BTreeNode(this.nextIndex++, this.starting_x, STARTING_Y);
		this.cmd("CreateBTreeNode",
				 this.treeRoot.graphicID, 
				 WIDTH_PER_ELEM, NODE_HEIGHT, 
				 1, 
				 this.starting_x, 
				 STARTING_Y, 
				 BACKGROUND_COLOR,  
				 FOREGROUND_COLOR);
		this.treeRoot.keys[0] = insertedValue;
		this.cmd("SetText", this.treeRoot.graphicID, insertedValue, 0);
	}
	else
	{
		if (this.preemptiveSplit)
		{
			if (this.treeRoot.numKeys == this.max_keys)
			{
				this.split(this.treeRoot)
				this.resizeTree();
				this.cmd("Step");
				
			}
			this.insertNotFull(this.treeRoot, insertedValue);				
		}
		else
		{
			this.insert(this.treeRoot, insertedValue);					
		}
		if (!this.treeRoot.isLeaf)
		{
			this.resizeTree();
		}
	}
	
	this.cmd("SetText", this.messageID, "");
	
	return this.commands;
	
}

BTree.prototype.insertNotFull = function(tree, insertValue)
{
	this.cmd("SetHighlight", tree.graphicID, 1);
	this.cmd("Step");
	if (tree.isLeaf)
	{
		this.cmd("SetText", this.messageID, "Inserting " + insertValue + ".  Inserting into a leaf");
		tree.numKeys++;
		this.cmd("SetNumElements", tree.graphicID, tree.numKeys);
		var insertIndex = tree.numKeys - 1;
		while (insertIndex > 0 && tree.keys[insertIndex - 1] > insertValue)
		{
			tree.keys[insertIndex] = tree.keys[insertIndex - 1];
			this.cmd("SetText", tree.graphicID, tree.keys[insertIndex], insertIndex);
			insertIndex--;
		}
		tree.keys[insertIndex] = insertValue;
		this.cmd("SetText", tree.graphicID, tree.keys[insertIndex], insertIndex);
		this.cmd("SetHighlight", tree.graphicID, 0);
		this.resizeTree();
	}
	else
	{
		var findIndex = 0;
		while (findIndex < tree.numKeys && tree.keys[findIndex] < insertValue)
		{
			findIndex++;					
		}				
		this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[findIndex].graphicID, 1);
		this.cmd("Step");
		this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[findIndex].graphicID, 0);
		this.cmd("SetHighlight", tree.graphicID, 0);
		if (tree.children[findIndex].numKeys == this.max_keys)
		{
			var newTree = this.split(tree.children[findIndex]);
			this.resizeTree();
			this.cmd("Step");
			this.insertNotFull(newTree, insertValue);
		}
		else
		{
			this.insertNotFull(tree.children[findIndex], insertValue);
		}
	}
}



BTree.prototype.insert = function(tree, insertValue)
{
	this.cmd("SetHighlight", tree.graphicID, 1);
	this.cmd("Step");
	if (tree.isLeaf)
	{
		this.cmd("SetText", this.messageID, "Inserting " + insertValue + ".  Inserting into a leaf");
		tree.numKeys++;
		this.cmd("SetNumElements", tree.graphicID, tree.numKeys);
		var insertIndex = tree.numKeys - 1;
		while (insertIndex > 0 && tree.keys[insertIndex - 1] > insertValue)
		{
			tree.keys[insertIndex] = tree.keys[insertIndex - 1];
			this.cmd("SetText", tree.graphicID, tree.keys[insertIndex], insertIndex);
			insertIndex--;
		}
		tree.keys[insertIndex] = insertValue;
		this.cmd("SetText", tree.graphicID, tree.keys[insertIndex], insertIndex);
		this.cmd("SetHighlight", tree.graphicID, 0);
		this.resizeTree();
		this.insertRepair(tree);
	}
	else
	{
		var findIndex = 0;
		while (findIndex < tree.numKeys && tree.keys[findIndex] < insertValue)
		{
			findIndex++;					
		}				
		this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[findIndex].graphicID, 1);
		this.cmd("Step");
		this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[findIndex].graphicID, 0);
		this.cmd("SetHighlight", tree.graphicID, 0);
		this.insert(tree.children[findIndex], insertValue);				
	}
}

BTree.prototype.insertRepair = function(tree) 
{
	if (tree.numKeys <= this.max_keys)
	{
		return;
	}
	else if (tree.parent == null)
	{
		this.treeRoot = this.split(tree);
		return;
	}
	else
	{
		var newNode  = this.split(tree);
		this.insertRepair(newNode);
	}			
}

BTree.prototype.split = function(tree)
{
	this.cmd("SetText", this.messageID, "Node now contains too many keys.  Splittig ...");
	this.cmd("SetHighlight", tree.graphicID, 1);
	this.cmd("Step");
	this.cmd("SetHighlight", tree.graphicID, 0);
	var rightNode = new BTreeNode(this.nextIndex++, tree.x + 100, tree.y);
	rightNode.numKeys = tree.numKeys - this.split_index - 1;
	var risingNode = tree.keys[this.split_index];
	
	
	if (tree.parent != null)
	{
		var currentParent = tree.parent;
		for (var parentIndex = 0; parentIndex < currentParent.numKeys + 1 && currentParent.children[parentIndex] != tree; parentIndex++);
		if (parentIndex == currentParent.numKeys + 1)
		{
			throw new Error("Couldn't find which child we were!");
		}
		this.cmd("SetNumElements", currentParent.graphicID, currentParent.numKeys + 1);
		for (i = currentParent.numKeys; i > parentIndex; i--)
		{
			currentParent.children[i+1] = currentParent.children[i];
			this.cmd("Disconnect", currentParent.graphicID, currentParent.children[i].graphicID);
			this.cmd("Connect", currentParent.graphicID,  currentParent.children[i].graphicID, FOREGROUND_COLOR, 
				0, // Curve
				0, // Directed
				"", // Label
				i+1);
			
			currentParent.keys[i] = currentParent.keys[i-1];
			this.cmd("SetText", currentParent.graphicID, currentParent.keys[i] ,i);
		}
		currentParent.numKeys++;
		currentParent.keys[parentIndex] = risingNode;
		this.cmd("SetText", currentParent.graphicID, "", parentIndex);
		this.moveLabel1ID = this.nextIndex++;
		this.cmd("CreateLabel", this.moveLabel1ID, risingNode, this.getLabelX(tree, this.split_index),  tree.y)
		this.cmd("SetForegroundColor", this.moveLabel1ID, FOREGROUND_COLOR);

		this.cmd("Move", this.moveLabel1ID,  this.getLabelX(currentParent, parentIndex),  currentParent.y)
		
		
		
		
		currentParent.children[parentIndex+1] = rightNode;
		rightNode.parent = currentParent;
		
	}
	
	
	this.cmd("CreateBTreeNode",
			  rightNode.graphicID, 
			  WIDTH_PER_ELEM, NODE_HEIGHT, 
			  tree.numKeys - this.split_index - 1, 
			  tree.x, 
			  tree.y,  
			  BACKGROUND_COLOR, 
			  FOREGROUND_COLOR);
	
	var i;
	for (i = this.split_index + 1; i < tree.numKeys + 1; i++)
	{
		rightNode.children[i - this.split_index - 1] = tree.children[i];
		if (tree.children[i] != null)
		{
			rightNode.isLeaf = false;
			this.cmd("Disconnect", tree.graphicID, tree.children[i].graphicID);
			
			this.cmd("Connect", rightNode.graphicID, 
				rightNode.children[i - this.split_index - 1].graphicID,
				FOREGROUND_COLOR,
				0, // Curve
				0, // Directed
				"", // Label
				i - this.split_index - 1);
			if (tree.children[i] != null)
			{
				tree.children[i].parent = rightNode;
			}
			tree.children[i] = null;
			
		}
	}
	for (i = this.split_index+1; i < tree.numKeys; i++)
	{
		rightNode.keys[i - this.split_index - 1] = tree.keys[i];
		this.cmd("SetText", rightNode.graphicID, rightNode.keys[i - this.split_index - 1], i - this.split_index - 1);
	}
	var leftNode = tree;
	leftNode.numKeys = this.split_index;
	// TO MAKE UNDO WORK -- CAN REMOVE LATER VV
	for (i = this.split_index; i < tree.numKeys; i++)
	{
		this.cmd("SetText", tree.graphicID, "", i); 
	}
	// TO MAKE UNDO WORK -- CAN REMOVE LATER ^^
	this.cmd("SetNumElements", tree.graphicID, this.split_index);
	
	if (tree.parent != null)
	{
		this.cmd("Connect", currentParent.graphicID, rightNode.graphicID, FOREGROUND_COLOR, 
			0, // Curve
			0, // Directed
			"", // Label
			parentIndex + 1);
		this.resizeTree();
		this.cmd("Step")
		this.cmd("Delete", this.moveLabel1ID);				
		this.cmd("SetText", currentParent.graphicID, risingNode, parentIndex);
		return tree.parent;
	}
	else //			if (tree.parent == null)
	{
		this.treeRoot = new BTreeNode(this.nextIndex++, this.starting_x, STARTING_Y);
		this.cmd("CreateBTreeNode",
				 this.treeRoot.graphicID, 
				 WIDTH_PER_ELEM, 
				 NODE_HEIGHT, 
				 1, 
				 this.starting_x, 
				 STARTING_Y,
				 BACKGROUND_COLOR,  
				 FOREGROUND_COLOR);
		this.treeRoot.keys[0] = risingNode;
		this.cmd("SetText", this.treeRoot.graphicID, risingNode, 0);
		this.treeRoot.children[0] = leftNode;
		this.treeRoot.children[1] = rightNode;
		leftNode.parent = this.treeRoot;
		rightNode.parent = this.treeRoot;
		this.cmd("Connect", this.treeRoot.graphicID, leftNode.graphicID, FOREGROUND_COLOR, 
			0, // Curve
			0, // Directed
			"", // Label
			0);	// Connection Point
		this.cmd("Connect", this.treeRoot.graphicID, rightNode.graphicID, FOREGROUND_COLOR, 
			0, // Curve
			0, // Directed
			"", // Label
			1); // Connection Point
		this.treeRoot.isLeaf = false;
		return this.treeRoot;
	}
	
	
	
}

BTree.prototype.deleteElement = function(deletedValue)
{
	this.commands = new Array();
	this.cmd("SetText", 0, "Deleting "+deletedValue);
	this.cmd("Step");
	this.cmd("SetText", 0, "");
	this.highlightID = this.nextIndex++;
	this.cmd("SetText", 0, "");
	if (this.preemptiveSplit)
	{
		this.doDeleteNotEmpty(this.treeRoot, deletedValue);
	}
	else
	{
		this.doDelete(this.treeRoot, deletedValue);
		
	}
	if (this.treeRoot.numKeys == 0)
	{
		this.cmd("Step");
		this.cmd("Delete", this.treeRoot.graphicID);
		this.treeRoot = this.treeRoot.children[0];
		this.treeRoot.parent = null;
		this.resizeTree();
	}
	return this.commands;						
}

BTree.prototype.doDeleteNotEmpty = function(tree, val)
{
	if (tree != null)
	{
		this.cmd("SetHighlight", tree.graphicID, 1);
		this.cmd("Step");
		var i;
		for (i = 0; i < tree.numKeys && tree.keys[i] < val; i++);
		if (i == tree.numKeys)
		{
			if (!tree.isLeaf)
			{
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[tree.numKeys].graphicID, 1);
				this.cmd("Step");
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[tree.numKeys].graphicID, 0);
				
				if (tree.children[tree.numKeys].numKeys == this.min_keys)
				{
					var nextNode;
					if (tree.children[tree.numKeys - 1].numKeys > this.min_keys)
					{
						nextNode = this.stealFromLeft(tree.children[tree.numKeys], tree.numKeys)
						this.doDeleteNotEmpty(nextNode, val);
					}
					else
					{
						nextNode = this.mergeRight(tree.children[tree.numKeys - 1])
						this.doDeleteNotEmpty(nextNode, val);
					}
				}
				else
				{
					this.doDeleteNotEmpty(tree.children[tree.numKeys], val);							
				}
			}
			else
			{
				this.cmd("SetHighlight", tree.graphicID, 0);
			}
		}
		else if (tree.keys[i] > val)
		{
			if (!tree.isLeaf)
			{
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 1);
				this.cmd("Step");
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 0);					
				
				if (tree.children[i].numKeys > this.min_keys)
				{
					this.doDeleteNotEmpty(tree.children[i], val);
				}
				else
				{
					if (tree.children[i+1].numKeys > this.min_keys)
					{
						nextNode = this.stealFromRight(tree.children[i], i);
						this.doDeleteNotEmpty(nextNode, val);
					}
					else
					{
						nextNode = this.mergeRight(tree.children[i]);
						this.doDeleteNotEmpty(nextNode, val);
					}
					
				}
			}
			else
			{
				this.cmd("SetHighlight", tree.graphicID, 0);
			}
		}
		else
		{
			this.cmd("SetTextColor", tree.graphicID, "FF0000", i);
			this.cmd("Step");
			if (tree.isLeaf)
			{
				this.cmd("SetTextColor", tree.graphicID, FOREGROUND_COLOR, i);
				for (var j = i; j < tree.numKeys - 1; j++)
				{
					tree.keys[j] = tree.keys[j+1];
					this.cmd("SetText", tree.graphicID, tree.keys[j], j);
				}
				tree.numKeys--;
				this.cmd("SetText", tree.graphicID, "", tree.numKeys);
				this.cmd("SetNumElements", tree.graphicID, tree.numKeys);
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.resizeTree();
				this.cmd("SetText", this.messageID, "");
				
				
			}
			else
			{
				this.cmd("SetText", this.messageID, "Checking to see if tree to left of element to delete \nhas an extra key");
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 1);					
				
				
				this.cmd("Step");
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 0);					
				var maxNode = tree.children[i];
				
				if (tree.children[i].numKeys == this.min_keys)
				{
					
					this.cmd("SetText", this.messageID, 
							 "Tree to left of element to delete does not have an extra key.  \nLooking to the right ...");
					this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i+1].graphicID, 1);
					this.cmd("Step");
					this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i + 1].graphicID, 0);	
					// Trees to left and right of node to delete don't have enough keys
					//   Do a merge, and then recursively delete the element
					if (tree.children[i+1].numKeys == this.min_keys)
					{
						this.cmd("SetText", this.messageID, 
								 "Neither subtree has extra nodes.  Mergeing around the key to delete, \nand recursively deleting ...");
						this.cmd("Step");
						this.cmd("SetTextColor", tree.graphicID, FOREGROUND_COLOR, i);
						nextNode = this.mergeRight(tree.children[i]);
						this.doDeleteNotEmpty(nextNode, val);
						return;
					}
					else
					{
						this.cmd("SetText", this.messageID, 
								 "Tree to right of element to delete does have an extra key. \nFinding the smallest key in that subtree ...");
						this.cmd("Step");
						
						var minNode = tree.children[i+1];
						while (!minNode.isLeaf)
						{
							
							this.cmd("SetHighlight", minNode.graphicID, 1);
							this.cmd("Step")
							this.cmd("SetHighlight", minNode.graphicID, 0);
							if (minNode.children[0].numKeys == this.min_keys)
							{
								if (minNode.children[1].numKeys == this.min_keys)
								{
									minNode = this.mergeRight(minNode.children[0]);
								}
								else
								{
									minNode = this.stealFromRight(minNode.children[0], 0);
								}
							}
							else
							{
								minNode = minNode.children[0];
							}
						}
						
						this.cmd("SetHighlight", minNode.graphicID, 1);
						tree.keys[i] = minNode.keys[0];
						this.cmd("SetTextColor", tree.graphicID, FOREGROUND_COLOR, i);
						this.cmd("SetText", tree.graphicID, "", i);
						this.cmd("SetText", minNode.graphicID, "", 0);
						
						this.cmd("CreateLabel", this.moveLabel1ID, minNode.keys[0], this.getLabelX(minNode, 0),  minNode.y)
						this.cmd("Move", this.moveLabel1ID, this.getLabelX(tree, i), tree.y);
						this.cmd("Step");
						this.cmd("Delete", this.moveLabel1ID);
						this.cmd("SetText", tree.graphicID, tree.keys[i], i);
						for (i = 1; i < minNode.numKeys; i++)
						{
							minNode.keys[i-1] = minNode.keys[i]
							this.cmd("SetText", minNode.graphicID, minNode.keys[i-1], i - 1);
						}
						this.cmd("SetText", minNode.graphicID, "",minNode.numKeys - 1);
						
						minNode.numKeys--;
						this.cmd("SetHighlight", minNode.graphicID, 0);
						this.cmd("SetHighlight", tree.graphicID, 0);
						
						this.cmd("SetNumElements", minNode.graphicID, minNode.numKeys);							
						this.resizeTree();
						this.cmd("SetText", this.messageID, "");
						
					}
				}
				else
				{
					
					this.cmd("SetText", this.messageID, 
							 "Tree to left of element to delete does have \nan extra key. Finding the largest key in that subtree ...");
					this.cmd("Step");
					while (!maxNode.isLeaf)
					{
						this.cmd("SetHighlight", maxNode.graphicID, 1);
						this.cmd("Step")
						this.cmd("SetHighlight", maxNode.graphicID, 0);
						if (maxNode.children[maxNode.numKeys].numKeys == this.min_keys)
						{
							if (maxNode.children[maxNode.numKeys - 1] > this.min_keys)
							{
								maxNode = this.stealFromLeft(maxNode.children[maxNode.numKeys], maxNode.numKeys);
							}
							else
							{
								
							}	maxNode = this.mergeRight(maxNode.children[maxNode.numKeys-1]);
						}
						else
						{
							maxNode = maxNode.children[maxNode.numKeys];
						}
					}
					this.cmd("SetHighlight", maxNode.graphicID, 1);
					tree.keys[i] = maxNode.keys[maxNode.numKeys - 1];
					this.cmd("SetTextColor", tree.graphicID, FOREGROUND_COLOR, i);
					this.cmd("SetText", tree.graphicID, "", i);
					this.cmd("SetText", maxNode.graphicID, "", maxNode.numKeys - 1);
					this.cmd("CreateLabel", this.moveLabel1ID, tree.keys[i], this.getLabelX(maxNode, maxNode.numKeys - 1),  maxNode.y)
					this.cmd("Move", this.moveLabel1ID, this.getLabelX(tree, i), tree.y);
					this.cmd("Step");
					this.cmd("Delete", this.moveLabel1ID);
					this.cmd("SetText", tree.graphicID, tree.keys[i], i);
					maxNode.numKeys--;
					this.cmd("SetHighlight", maxNode.graphicID, 0);
					this.cmd("SetHighlight", tree.graphicID, 0);
					
					this.cmd("SetNumElements", maxNode.graphicID, maxNode.numKeys);
					this.resizeTree();
					this.cmd("SetText", this.messageID, "");
					
				}
				
			}
		}
		
	}
}		


BTree.prototype.doDelete = function(tree, val)
{
	if (tree != null)
	{
		this.cmd("SetHighlight", tree.graphicID, 1);
		this.cmd("Step");
		var i;
		for (i = 0; i < tree.numKeys && tree.keys[i] < val; i++);
		if (i == tree.numKeys)
		{
			if (!tree.isLeaf)
			{
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[tree.numKeys].graphicID, 1);
				this.cmd("Step");
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[tree.numKeys].graphicID, 0);
				this.doDelete(tree.children[tree.numKeys], val);
			}
			else
			{
				this.cmd("SetHighlight", tree.graphicID, 0);
			}
		}
		else if (tree.keys[i] > val)
		{
			if (!tree.isLeaf)
			{
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 1);
				this.cmd("Step");
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.cmd("SetEdgeHighlight", tree.graphicID, tree.children[i].graphicID, 0);					
				this.doDelete(tree.children[i], val);
			}
			else
			{
				this.cmd("SetHighlight", tree.graphicID, 0);
			}
		}
		else
		{
			this.cmd("SetTextColor", tree.graphicID, "#FF0000", i);
			this.cmd("Step");
			if (tree.isLeaf)
			{
				this.cmd("SetTextColor", tree.graphicID, FOREGROUND_COLOR, i);
				for (var j = i; j < tree.numKeys - 1; j++)
				{
					tree.keys[j] = tree.keys[j+1];
					this.cmd("SetText", tree.graphicID, tree.keys[j], j);
				}
				tree.numKeys--;
				this.cmd("SetText", tree.graphicID, "", tree.numKeys);
				this.cmd("SetNumElements", tree.graphicID, tree.numKeys);
				this.cmd("SetHighlight", tree.graphicID, 0);
				this.repairAfterDelete(tree);
			}
			else
			{
				var maxNode = tree.children[i];
				while (!maxNode.isLeaf)
				{
					this.cmd("SetHighlight", maxNode.graphicID, 1);
					this.cmd("Step")
					this.cmd("SetHighlight", maxNode.graphicID, 0);
					maxNode = maxNode.children[maxNode.numKeys];
				}
				this.cmd("SetHighlight", maxNode.graphicID, 1);
				tree.keys[i] = maxNode.keys[maxNode.numKeys - 1];
				this.cmd("SetTextColor", tree.graphicID, FOREGROUND_COLOR, i);
				this.cmd("SetText", tree.graphicID, "", i);
				this.cmd("SetText", maxNode.graphicID, "", maxNode.numKeys - 1);
				this.cmd("CreateLabel", this.moveLabel1ID, tree.keys[i], this.getLabelX(maxNode, maxNode.numKeys - 1),  maxNode.y)
				this.cmd("Move", this.moveLabel1ID, this.getLabelX(tree, i), tree.y);
				this.cmd("Step");
				this.cmd("Delete", this.moveLabel1ID);
				this.cmd("SetText", tree.graphicID, tree.keys[i], i);
				maxNode.numKeys--;
				this.cmd("SetHighlight", maxNode.graphicID, 0);
				this.cmd("SetHighlight", tree.graphicID, 0);
				
				this.cmd("SetNumElements", maxNode.graphicID, maxNode.numKeys);
				this.repairAfterDelete(maxNode);					
			}
		}
		
	}
}



BTree.prototype.mergeRight = function(tree) 
{
	this.cmd("SetText", this.messageID, "Merging node");
	
	var parentNode = tree.parent;
	var parentIndex = 0;
	for (parentIndex = 0; parentNode.children[parentIndex] != tree; parentIndex++);
	var rightSib = parentNode.children[parentIndex+1];
	this.cmd("SetHighlight", tree.graphicID, 1);
	this.cmd("SetHighlight", parentNode.graphicID, 1);
	this.cmd("SetHighlight", rightSib.graphicID, 1);
	
	this.cmd("Step");
	this.cmd("SetNumElements", tree.graphicID, tree.numKeys + rightSib.numKeys + 1);
	tree.x = (tree.x + rightSib.x) / 2
	this.cmd("SetPosition", tree.graphicID, tree.x,  tree.y);
	
	tree.keys[tree.numKeys] = parentNode.keys[parentIndex];
	var fromParentIndex = tree.numKeys;
	//this.cmd("SetText", tree.graphicID, tree.keys[tree.numKeys], tree.numKeys);
	this.cmd("SetText", tree.graphicID, "", tree.numKeys);
	this.cmd("CreateLabel", this.moveLabel1ID, parentNode.keys[parentIndex],  this.getLabelX(parentNode, parentIndex),  parentNode.y);
	
	
	for (var i = 0; i < rightSib.numKeys; i++)
	{
		tree.keys[tree.numKeys + 1 + i] = rightSib.keys[i];
		this.cmd("SetText", tree.graphicID, tree.keys[tree.numKeys + 1 + i], tree.numKeys + 1 + i);
		this.cmd("SetText", rightSib.graphicID, "", i);
	}
	if (!tree.isLeaf)
	{
		for (i = 0; i <= rightSib.numKeys; i++)
		{
			this.cmd("Disconnect", rightSib.graphicID, rightSib.children[i].graphicID);
			tree.children[tree.numKeys + 1 + i] = rightSib.children[i];
			tree.children[tree.numKeys + 1 + i].parent = tree;
			this.cmd("Connect", tree.graphicID, 
				tree.children[tree.numKeys + 1 + i].graphicID,
				FOREGROUND_COLOR,
				0, // Curve
				0, // Directed
				"", // Label
				tree.numKeys + 1 + i);
		}
	}
	this.cmd("Disconnect", parentNode.graphicID, rightSib.graphicID);
	for (i = parentIndex+1; i < parentNode.numKeys; i++)
	{
		this.cmd("Disconnect", parentNode.graphicID, parentNode.children[i+1].graphicID);
		parentNode.children[i] = parentNode.children[i+1];
		this.cmd("Connect", parentNode.graphicID, 
			parentNode.children[i].graphicID,
			FOREGROUND_COLOR,
			0, // Curve
			0, // Directed
			"", // Label
			i);
		parentNode.keys[i-1] = parentNode.keys[i];
		this.cmd("SetText", parentNode.graphicID, parentNode.keys[i-1], i-1);					
	}
	this.cmd("SetText", parentNode.graphicID, "", parentNode.numKeys - 1);
	parentNode.numKeys--;
	this.cmd("SetNumElements", parentNode.graphicID, parentNode.numKeys);
	this.cmd("SetHighlight", tree.graphicID, 0);
	this.cmd("SetHighlight", parentNode.graphicID, 0);
//	this.cmd("SetHighlight", rightSib.graphicID, 0);
	
//	this.cmd("Step");
	this.cmd("Delete", rightSib.graphicID);
	tree.numKeys = tree.numKeys + rightSib.numKeys + 1;
	this.cmd("Move", this.moveLabel1ID, this.getLabelX(tree, fromParentIndex), tree.y);
	
	this.cmd("Step");
	// resizeTree();
	this.cmd("Delete", this.moveLabel1ID);
	this.cmd("SetText", tree.graphicID, tree.keys[fromParentIndex], fromParentIndex);
	
	this.cmd("SetText", this.messageID, "");
	return tree;
}


BTree.prototype.stealFromRight = function(tree, parentIndex) 
{
	// Steal from right sibling
	var parentNode = tree.parent;
	
	this.cmd("SetNumElements", tree.graphicID, tree.numKeys+1);					
	
	this.cmd("SetText", this.messageID, "Stealing from right sibling");
	
	var rightSib = parentNode.children[parentIndex + 1];
	tree.numKeys++;
	
	this.cmd("SetNumElements", tree.graphicID, tree.numKeys);
	
	
	
	
	
	
	
	this.cmd("SetText", tree.graphicID, "",  tree.numKeys - 1);
	this.cmd("SetText", parentNode.graphicID, "", parentIndex);
	this.cmd("SetText", rightSib.graphicID, "", 0);

	var tmpLabel1 = this.nextIndex++;
	var tmpLabel2 = this.nextIndex++;

	
	this.cmd("CreateLabel", tmpLabel1, rightSib.keys[0], this.getLabelX(rightSib, 0),  rightSib.y)
	this.cmd("CreateLabel", tmpLabel2, parentNode.keys[parentIndex], this.getLabelX(parentNode, parentIndex),  parentNode.y)
	this.cmd("SetForegroundColor", tmpLabel1, FOREGROUND_COLOR);
	this.cmd("SetForegroundColor", tmpLabel2, FOREGROUND_COLOR);
	
	this.cmd("Move", tmpLabel1, this.getLabelX(parentNode, parentIndex),  parentNode.y);
	this.cmd("Move", tmpLabel2, this.getLabelX(tree, tree.numKeys - 1), tree.y);
	
	this.cmd("Step")
	this.cmd("Delete", tmpLabel1);
	this.cmd("Delete", tmpLabel2);
	tree.keys[tree.numKeys - 1] = parentNode.keys[parentIndex];
	parentNode.keys[parentIndex] = rightSib.keys[0];
	
	
	
	this.cmd("SetText", tree.graphicID, tree.keys[tree.numKeys - 1], tree.numKeys - 1);
	this.cmd("SetText", parentNode.graphicID, parentNode.keys[parentIndex], parentIndex);
	if (!tree.isLeaf)
	{
		tree.children[tree.numKeys] = rightSib.children[0];
		tree.children[tree.numKeys].parent = tree;
		this.cmd("Disconnect", rightSib.graphicID, rightSib.children[0].graphicID);
		this.cmd("Connect", tree.graphicID, 
			tree.children[tree.numKeys].graphicID,
			FOREGROUND_COLOR,
			0, // Curve
			0, // Directed
			"", // Label
			tree.numKeys);	
		// TODO::CHECKME!
		
		for (var i = 1; i < rightSib.numKeys + 1; i++)
		{
			this.cmd("Disconnect", rightSib.graphicID, rightSib.children[i].graphicID);
			rightSib.children[i-1] = rightSib.children[i];
			this.cmd("Connect", rightSib.graphicID, 
				rightSib.children[i-1].graphicID,
				FOREGROUND_COLOR,
				0, // Curve
				0, // Directed
				"", // Label
				i-1);								
		}
		
	}
	for (i = 1; i < rightSib.numKeys; i++)
	{
		rightSib.keys[i-1] = rightSib.keys[i];
		this.cmd("SetText", rightSib.graphicID, rightSib.keys[i-1], i-1);
	}
	this.cmd("SetText", rightSib.graphicID, "", rightSib.numKeys-1);
	rightSib.numKeys--;
	this.cmd("SetNumElements", rightSib.graphicID, rightSib.numKeys);
	this.resizeTree();
	this.cmd("SetText", this.messageID, "");
	return tree;
	
}


BTree.prototype.stealFromLeft = function(tree, parentIndex) 
{
	var parentNode = tree.parent;
	// Steal from left sibling
	tree.numKeys++;
	this.cmd("SetNumElements", tree.graphicID, tree.numKeys);
	this.cmd("SetText", this.messageID, "Node has too few keys.  Stealing from left sibling.");
	
	for (i = tree.numKeys - 1; i > 0; i--)
	{
		tree.keys[i] = tree.keys[i-1];
		this.cmd("SetText", tree.graphicID, tree.keys[i], i);
	}
	var leftSib = parentNode.children[parentIndex -1];
	
	this.cmd("SetText", tree.graphicID, "", 0);
	this.cmd("SetText", parentNode.graphicID, "", parentIndex - 1);
	this.cmd("SetText", leftSib.graphicID, "", leftSib.numKeys - 1);
	
	var tmpLabel1 = this.nextIndex++;
	var tmpLabel2 = this.nextIndex++;
	
	this.cmd("CreateLabel", tmpLabel1, leftSib.keys[leftSib.numKeys - 1], this.getLabelX(leftSib, leftSib.numKeys - 1),  leftSib.y)
	this.cmd("CreateLabel", tmpLabel2, parentNode.keys[parentIndex - 1], this.getLabelX(parentNode, parentIndex - 1),  parentNode.y)
	this.cmd("SetForegroundColor", tmpLabel1, FOREGROUND_COLOR);
	this.cmd("SetForegroundColor", tmpLabel2, FOREGROUND_COLOR);

	
	this.cmd("Move", tmpLabel1, this.getLabelX(parentNode, parentIndex - 1),  parentNode.y);
	this.cmd("Move", tmpLabel2, this.getLabelX(tree, 0), tree.y);
	
	this.cmd("Step")
	this.cmd("Delete", tmpLabel1);
	this.cmd("Delete", tmpLabel2);
	
	
	if (!tree.isLeaf)
	{
		for (var i = tree.numKeys; i > 0; i--)
		{
			this.cmd("Disconnect", tree.graphicID, tree.children[i-1].graphicID);
			tree.children[i] =tree.children[i-1];
			this.cmd("Connect", tree.graphicID, 
				tree.children[i].graphicID,
				FOREGROUND_COLOR,
				0, // Curve
				0, // Directed
				"", // Label
				i);
		}
		tree.children[0] = leftSib.children[leftSib.numKeys];
		this.cmd("Disconnect", leftSib.graphicID, leftSib.children[leftSib.numKeys].graphicID);
		this.cmd("Connect", tree.graphicID, 
			tree.children[0].graphicID,
			FOREGROUND_COLOR,
			0, // Curve
			0, // Directed
			"", // Label
			0);
		leftSib.children[leftSib.numKeys] = null;
		tree.children[0].parent = tree;
		
	}
	
	tree.keys[0] = parentNode.keys[parentIndex - 1];
	this.cmd("SetText", tree.graphicID, tree.keys[0], 0);						
	parentNode.keys[parentIndex-1] = leftSib.keys[leftSib.numKeys - 1];
	this.cmd("SetText", parentNode.graphicID, parentNode.keys[parentIndex - 1], parentIndex - 1);
	this.cmd("SetText", leftSib.graphicID,"", leftSib.numKeys - 1);
	
	leftSib.numKeys--;
	this.cmd("SetNumElements", leftSib.graphicID, leftSib.numKeys);
	this.resizeTree();
	this.cmd("SetText", this.messageID, "");
	return tree;
}


BTree.prototype.repairAfterDelete = function(tree)
{
	if (tree.numKeys < this.min_keys)
	{
		if (tree.parent == null)
		{
			if (tree.numKeys == 0)
			{
				this.cmd("Step");
				this.cmd("Delete", tree.graphicID);
				this.treeRoot = tree.children[0];
				if (this.treeRoot != null)
					this.treeRoot.parent = null;
				this.resizeTree();
			}
		}
		else
		{
			var parentNode = tree.parent;
			for (var parentIndex = 0; parentNode.children[parentIndex] != tree; parentIndex++);
			if (parentIndex > 0 && parentNode.children[parentIndex - 1].numKeys > this.min_keys)
			{
				this.stealFromLeft(tree, parentIndex);
				
			}
			else if (parentIndex < parentNode.numKeys && parentNode.children[parentIndex + 1].numKeys > this.min_keys)
			{
				this.stealFromRight(tree,parentIndex);
				
			}
			else if (parentIndex == 0)
			{
				// Merge with right sibling
				var nextNode = this.mergeRight(tree);
				this.repairAfterDelete(nextNode.parent);			
			}
			else
			{
				// Merge with left sibling
				nextNode = this.mergeRight(parentNode.children[parentIndex-1]);
				this.repairAfterDelete(nextNode.parent);			
				
			}
			
			
		}
	}
}

BTree.prototype.getLabelX = function(tree, index) 
{
	return tree.x - WIDTH_PER_ELEM * tree.numKeys / 2 + WIDTH_PER_ELEM / 2 + index * WIDTH_PER_ELEM;
}

BTree.prototype.resizeTree = function()
{
	this.resizeWidths(this.treeRoot);
	this.setNewPositions(this.treeRoot, this.starting_x, STARTING_Y);
	this.animateNewPositions(this.treeRoot);
}

BTree.prototype.setNewPositions = function(tree, xPosition, yPosition)
{
	if (tree != null)
	{
		tree.y = yPosition;
		tree.x = xPosition;
		if (!tree.isLeaf)
		{
			var leftEdge = xPosition - tree.width / 2;
			var priorWidth = 0;
			for (var i = 0; i < tree.numKeys+1; i++)
			{
				this.setNewPositions(tree.children[i], leftEdge + priorWidth + tree.widths[i] / 2, yPosition+HEIGHT_DELTA);
				priorWidth += tree.widths[i];
			}
		}				
	}			
}

BTree.prototype.animateNewPositions = function(tree)
{
	if (tree == null)
	{
		return;
	}
	var i;
	for (i = 0; i < tree.numKeys + 1; i++)
	{
		this.animateNewPositions(tree.children[i]);
	}
	this.cmd("Move", tree.graphicID, tree.x, tree.y);
}

BTree.prototype.resizeWidths = function(tree) 
{
	if (tree == null)
	{
		return 0;
	}
	if (tree.isLeaf)
	{
		for (var i = 0; i < tree.numKeys + 1; i++)
		{
			tree.widths[i] = 0;
		}
		tree.width = tree.numKeys * WIDTH_PER_ELEM + NODE_SPACING;
		return tree.width;				
	}
	else
	{
		var treeWidth = 0;
		for (i = 0; i < tree.numKeys+1; i++)
		{
			tree.widths[i] = this.resizeWidths(tree.children[i]);
			treeWidth = treeWidth + tree.widths[i];
		}
		treeWidth = Math.max(treeWidth, tree.numKeys * WIDTH_PER_ELEM + NODE_SPACING);
		tree.width = treeWidth;
		return treeWidth;
	}
}
	



function BTreeNode(id, initialX, initialY)
{
	this.widths = [];
	this.keys = [];
	this.children = [];
	this.x = initialX;
	this.y = initialY;
	this.graphicID = id;
	this.numKeys = 1;
	this.isLeaf = true;
	this.parent = null;
	
	this.leftWidth = 0;
	this.rightWidth = 0;
	
}





var currentAlg;

function init()
{
	var animManag = initCanvas();
	currentAlg = new BTree(animManag, canvas.width, canvas.height);
}
