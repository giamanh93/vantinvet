/*
Class: Slider
        Creates a slider with two elements: a knob and a container. Returns the values.
Note:
        The Slider requires an XHTML doctype.
Arguments:
        element - the knob container
        knob - the handle
        options - see Options below
        maxknob - an optional maximum slider handle
Options:
		start - the minimum value for your slider.
		end - the maximum value for your slider.
        mode - either 'horizontal' or 'vertical'. defaults to horizontal.
        offset - relative offset for knob position. default to 0.
        knobheight - positions the max slider knob
		snap - whether the slider will slide in steps 
		numsteps - number of slide steps 
Events:
        onChange - a function to fire when the value changes.
        onComplete - a function to fire when you're done dragging.
        onTick - optionally, you can alter the onTick behavior, for example displaying an effect of the knob moving to the desired position.
                Passes as parameter the new position.
*/
var Cfslider = new Class({
	options: {
		onChange: Class.empty,
		onComplete: function(){		
			
		},
		onTick: function(pos){
			this.moveKnob.setStyle(this.p, pos);
		},
		start: 0,
		end: 1000,
		offset: 0,
		knobheight: 20,
		knobwidth: 14,
		mode: 'horizontal',
		clip_w:0,
		clip_l:0,
		isinit:true,
		snap: false,
		range: false,
		numsteps:null,
		module_id:null
	},
    initialize: function(key,module_id,options) {
    
    	var el=key+'_slider_gutter_m';
    	var knob=key+'_knob_from';
    	var maxknob=key+'_knob_to';
    	var bkg=key+'_slider_bkg_img';
    	
    	this.module_id=module_id;
		this.setOptions(options);
		this.element = document.id(el);
		this.knob = document.id(knob);
		this.previousChange = this.previousEnd = this.step = 0;
		this.bkg = document.id(bkg);
		if(this.options.steps==null){
			this.options.steps = this.options.end - this.options.start;
		}
		if(maxknob!=null)
			this.maxknob = document.id(maxknob);
		//else
		//	this.element.addEvent('mousedown', this.clickedElement.bindWithEvent(this));
		var mod, offset;
		switch(this.options.mode){
			case 'horizontal':
				this.z = 'x';
				this.p = 'left';
				mod = {'x': 'left', 'y': false};
				offset = 'offsetWidth';
				break;
			case 'vertical':
				this.z = 'y';
				this.p = 'top';
				mod = {'x': false, 'y': 'top'};
				offset = 'offsetHeight';
		}//alert(this.options.offset);
		this.max = this.element[offset] - this.knob[offset] + (this.options.offset * 2);		
		this.half = this.knob[offset]/2;
		this.full = this.element[offset] - this.knob[offset] + (this.options.offset * 2);
		this.min = !!(this.options.range[0] || this.options.range[0] === 0) ? this.options.range[0] : 0;
		this.getPos = this.element['get' + this.p.capitalize()].bind(this.element);
		this.knob.setStyle('position', 'relative').setStyle(this.p, - this.options.offset);

		this.range = this.max - this.min;
		this.steps = this.options.steps || this.full;
		this.stepSize = Math.abs(this.range) / this.steps;
		this.stepWidth = this.stepSize * this.full / Math.abs(this.range); 
		
		//indicate whether the min or max has been moved
		this.max_moved=false;
		this.min_moved=false;
		
		if(maxknob != null) {
			this.maxPreviousChange = -1;
			this.maxPreviousEnd = -1;
			this.maxstep = this.options.end;
			this.maxknob.setStyle('position', 'relative').setStyle(this.p, + this.max - this.options.offset).setStyle('bottom', this.options.knobheight);
		}
		var lim = {};
		//status = this.z
		lim[this.z] = [- this.options.offset, this.max - this.options.offset];
		//lim[this.z] = [100, this.max - this.options.offset];

		this.drag = new Drag(this.knob, {
			limit: lim,
			modifiers: mod,
			snap: 0,
			onStart: function(){
					this.draggedKnob();
			}.bind(this),
			onDrag: function(){
					this.draggedKnob();
			}.bind(this),
			onComplete: function(){
					this.draggedKnob();
					this.end();
			}.bind(this)
		});
		if(maxknob != null) {  
			this.maxdrag = new Drag(this.maxknob, {
				limit: lim,
				modifiers: mod,
				snap: 0, 
				onStart: function(){
					this.draggedKnob(1);
				}.bind(this),
				onDrag: function(){
					this.draggedKnob(1);
				}.bind(this),
				onComplete: function(){
					this.draggedKnob(1);
					this.end();
				}.bind(this)
			});		
		}

		if (this.options.snap) {
			//this.drag.options.grid = Math.ceil(this.stepWidth);
			this.drag.options.grid = (this.full)/this.options.numsteps ;
			this.drag.options.limit[this.z][1] = this.full;
			//this.drag.options.grid = this.drag.options.grid - (this.knob[offset]/this.options.numsteps);
			status = "GRID - " + this.drag.options.grid  + "  , full = " + this.full;// DEBUG

		}
		if (this.options.initialize) this.options.initialize.call(this);
    },
	setMin: function(stepMin){ 
		this.step = stepMin.limit(this.options.start, this.options.end);
		this.checkStep();
		this.end(true);
		this.moveKnob = this.knob;
		this.bkg.style.clip = "rect(0px "+  (parseInt(this.toPosition(this.step)) +3) + "px 10px 0px)";
		status =this.bkg.style.clip + "  vl= " + parseInt(this.toPosition(this.step)) ; //Debug
		this.fireEvent('onTick', this.toPosition(this.step));
		return this;
	},
	setMax: function(stepMax){
		this.maxstep = stepMax.limit(this.options.start, this.options.end);
		this.checkStep(1);
		this.end(true);
		this.moveKnob = this.maxknob;
		var w= Math.abs(this.toPosition(this.step)- this.toPosition(this.maxstep)) + 3 ;
		var r = parseInt(this.clip_l + w); 
		this.bkg.style.clip = "rect(0px "+  r + "px 10px "+ this.clip_l + "px)";

		this.fireEvent('onTick', this.toPosition(this.maxstep));
		// For Init Only 
		if(this.options.isinit){
			var lim = {}; var mi,mx;
			mi = - this.options.offset; 
			mx= parseInt(this.maxknob.getStyle('left')) - this.options.offset-4 ;
			lim[this.z] = [mi, mx];
			this.drag.options.limit = lim;
			this.options.isinit = false;
		}
		return this; 
	},
	clickedElement: function(event){
		var position = event.page[this.z] - this.getPos() - this.half;
		position = position.limit(-this.options.offset, this.max -this.options.offset);

		this.step = this.toStep(position);

		//this.moveKnob = this.knob;
		this.bkg.style.clip = "rect(0px "+  (parseInt(this.toPosition(this.step)) +3) + "px 10px 0px)";  
		//status =this.bkg.style.clip; //Debug
		this.checkStep();
		this.end();
		this.fireEvent('onTick', position);
	},
/* This function is called every time a knob is moved.
 * Its calling the checkstep function which checks to have valid movements
 * */
	draggedKnob: function(mx){
		var lim = {}; var mi,mx; 
		if(mx==null) {//alert(this.drag.value.now[this.z]);
			this.step =this.toStep(this.drag.value.now[this.z]);	 
			this.checkStep();			
		}else {
			this.maxstep = this.toStep(this.maxdrag.value.now[this.z]); 
			this.checkStep(1);
		}
		
		//modification to fire  event only when the slider is moved with the mouse
		if(this.step <= this.maxstep){
			
			if(this.maxstep==this.options.end && this.max_moved==false){}
			else this.max_moved=true;
			
			if(this.step==this.options.start && this.min_moved==false){}
			else this.min_moved=true;
			this.fireEvent('onMouseMove', { minpos: this.step, maxpos: this.maxstep, max_moved:this.max_moved, min_moved:this.min_moved });
		}else{
			this.fireEvent('onMouseMove', { minpos: this.maxstep, maxpos: this.step });
			//this.clip_l = (parseInt(this.maxknob.getStyle('left')) + 10) ;
		}	
	},
	checkStep: function(mx){
		var lim = {}; var mi,mx;
		var limm = {};

		if(mx==null) {
			if (this.previousChange != this.step){
				this.previousChange = this.step;
			}
		}
		else {
			if (this.maxPreviousChange != this.maxstep){
				this.maxPreviousChange = this.maxstep;
			}
		}

		if(this.maxknob!=null) {
			
			//from knob
			mi = - this.options.offset; // takes role to decide the minimum value of the "from" knob
			mx= parseInt(this.maxknob.getStyle('left'));// the maximum value of the "from" knob
			lim[this.z] = [mi, mx];
			this.drag.options.limit = lim;
		
			//to knob
			mi = parseInt(this.knob.getStyle('left')); 
			mx=this.max  - this.options.offset;
			limm[this.z] = [mi, mx];
			this.maxdrag.options.limit = limm; 

			if(this.step <= this.maxstep){
				
				if(this.maxstep==this.options.end && this.max_moved==false){}
				else this.max_moved=true;
				
				if(this.step==this.options.start && this.min_moved==false){}
				else this.min_moved=true;
				
				
				this.fireEvent('onChange', { minpos: this.step, maxpos: this.maxstep, max_moved:this.max_moved, min_moved:this.min_moved });
				//this.clip_l = parseInt(this.knob.getStyle('left'));
			}
			else{
				this.fireEvent('onChange', { minpos: this.maxstep, maxpos: this.step });
				//this.clip_l = (parseInt(this.maxknob.getStyle('left')) + 10) ;
			}	
			this.clip_l = parseInt(this.knob.getStyle('left')) + 18;
			//var w = Math.abs(parseInt(this.knob.getStyle('left')) - parseInt(this.maxknob.getStyle('left'))) + 3;	
			var w = Math.abs(parseInt(this.knob.getStyle('left')) - parseInt(this.maxknob.getStyle('left')));
			//if(w > 3) w = w+3;
			
			var r = parseInt(this.clip_l + w); 
			this.bkg.style.clip = "rect(0px "+  r + "px 10px "+ this.clip_l + "px)" ; 
			//alert(this.bkg.getStyle('clip'));
			//status =this.bkg.style.clip  + " w= " + w //Debug

		}else {  
			this.fireEvent('onChange', this.step);
			this.bkg.style.clip = "rect(0px "+  (parseInt(this.drag.value.now[this.z]) +3)  + "px 10px 0px)";
		}
	},
	//set indicates if the function is called by the setMin and setMax functions
	//we should take control of that in order not to have infinitive loops with the ajax calls
	end: function(set){
		if (this.previousEnd !== this.step || (this.maxknob != null && this.maxPreviousEnd != this.maxstep)) {
			this.previousEnd = this.step;
			if(this.maxknob != null) {
				this.maxPreviousEnd = this.maxstep;
				if(this.step <= this.maxstep && !set)
					this.fireEvent('onComplete', { minpos: this.step + '', maxpos: this.maxstep + '' });
				else if(!set)this.fireEvent('onComplete', { minpos: this.maxstep + '', maxpos: this.step + '' });
			}else if(!set){  
				this.fireEvent('onComplete', this.step + '');
			}
		}
	},
	
	toStep: function(position){//alert(position);
		//step=10;
		step=Math.round((position + this.options.offset) / this.max * this.options.steps) + this.options.start;
		//alert(step);
		return step;
	},

	toPosition: function(step){
		var pos=(this.max * step / this.options.steps) - (this.max * this.options.start / this.options.steps) - this.options.offset;
		return pos;
	}

});

Cfslider.implement(new Events);
Cfslider.implement(new Options);