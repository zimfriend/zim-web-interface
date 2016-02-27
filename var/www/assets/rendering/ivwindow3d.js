

var mouseCancelPopup = false;
var g_mouseDownWindow=null;

function ivSetEvent(d,e,f)
{
	if (d.attachEvent) //if IE (and Opera depending on user setting)
		d.attachEvent("on"+e, f);
	else if (d.addEventListener) //WC3 browsers
		d.addEventListener(e, f);
}

function ivwindow3d(canvas,file,color,path)
{
	this.m_canvas=canvas;
	canvas.ivwindow3d=this;
	this.mvMatrix = mat4.create();
	this.viewFrom=[0, 0, 6];
	this.viewTo=[0, 0, 0];
	this.viewUp=[0, 1, 0];
	this.viewFar=100,viewNear=0.1;
	this.fov=90;
	this.LX = null;
	this.LY = null;
	this.lastTouchDistance = -1;
	this.mouseMoved=false;
	this.m_color=(color!=undefined)?color:0x7f7f7f;
	this.m_bOk=this.initHardware();
	this.vpVersion=0;

	this.timer=false;
	this.m_cameramode=0;// 0 default rotate, 1 - zoom, 2 -pane
	if(this.m_bOk)
	{
		if(file)this.loadSpace(file,path);else this.space=0;
		this.gl.enable(this.gl.DEPTH_TEST);
	//if(document.touchmove!=undefined)
{
   ivSetEvent(canvas,"touchstart",handleTouchStart);
   ivSetEvent(document,"touchmove",handleTouchMove);
   ivSetEvent(document,"touchend",handleTouchEnd);
   ivSetEvent(document,"touchcancel",handleTouchCancel);
}
		var mousewheelevt=(/Firefox/i.test(navigator.userAgent))? "DOMMouseScroll" : "mousewheel"
		ivSetEvent(canvas,mousewheelevt,handleMouseWheel);
		canvas.oncontextmenu = handleContextMenu;
		canvas.onmousedown = handleMouseDown;
		canvas.onmousemove = handleMouseMove2;
		document.onmouseup = handleMouseUp;
		document.onmousemove = handleMouseMove;
		document.onselectstart = handleSelectStart;// IE
		this.invalidate();
	}
}

ivwindow3d.prototype.getWindow = function(){return this;}
ivwindow3d.prototype.initHardware = function()
{
	var n = ["webgl", "experimental-webgl", "webkit-3d", "moz-webgl"];
	for (var i = 0; i < n.length; i++) 
	{
		try {
			this.gl = this.m_canvas.getContext(n[i],{alpha:false});
		}catch (e) {   }
		if(this.gl)
		{
			this.gl.viewportWidth = this.m_canvas.width;this.gl.viewportHeight = this.m_canvas.height;this.m_bOk=true;
			break;
		}
	}
	if (!this.gl) {
		// alert("Could not initialise WebGL"); //DIY disable default warning of WebGL - PNI
	}
	return this.gl!=null;
}



ivwindow3d.prototype.setViewImp =function(v)
{
	if(v)
	{
		this.viewFrom=v.org.slice();
		this.viewTo=v.target.slice();
		this.viewUp=v.up.slice();
		if(v.fov)
			this.fov=v.fov/2;
		else
			this.fov=90;

		if(v.far)this.viewFar=v.far;
		if(v.near)this.viewNear=v.near;
		if(this.space)
			this.invalidate(IV.INV_VERSION);
	}
}

ivwindow3d.prototype.setDefView =function()
{
	this.viewFar=0.1;
	this.viewNear=100;
	this.setViewImp(this.space.view);
}

ivwindow3d.prototype.loadSpace=function (file,path)
{
	this.space=new space3d(this,this.gl);
	if(path!=undefined)this.space.path=path;
	var request = CreateRequest(file,path);
	request.ivspace=this.space;
	request.ivwnd=this;
	request.onreadystatechange = function () {
		if (this.readyState == 4 && this.status==200) {
			this.ivspace.load(JSON.parse(this.responseText));
			this.ivwnd.setDefView();
		}
	}
	request.send();
}

ivwindow3d.prototype.getDoubleSided = function(){return this.space.cfgDbl;}
ivwindow3d.prototype.setDoubleSided = function(b){if(this.space.cfgDbl!=b){var s=this.space;s.cfgDbl=b;s.invalidate(IV.INV_MTLS);}}
ivwindow3d.prototype.getMaterials= function(){return this.space.cfgDefMtl==null;}
ivwindow3d.prototype.setMaterials= function(b)
{
	var s=this.space;
	if(b)
	{
		if(s.cfgDefMtl){s.cfgDefMtl=null;this.invalidate();}
	}else{
		if(!s.cfgDefMtl)
		{
			s.cfgDefMtl={"diffuse":[0.8,0.8,0.8],"specular":[0.5,0.5,0.5],"ambient":[0.02,0.02,0.02],"phong":25.6};
			this.invalidate();
		};
	}
};

ivwindow3d.prototype.getTextures = function(){return this.space.cfgTextures;}
ivwindow3d.prototype.setTextures = function(b){if(this.space.cfgTextures!=b){this.space.cfgTextures=b;this.invalidate();}}
ivwindow3d.prototype.setLights=function(l)
{
	var s=this.space;
	s.lights=l;
	s.invalidate(IV.INV_MTLS);
}

function handleTouchStart(event)
{
	var view=event.currentTarget.ivwindow3d;
	if(view){
		view.OnMouseDown(event,true);
		g_mouseDownWindow=view;
		event.preventDefault();
	}
	mouseCancelPopup=false;
}

function handleTouchMove(event)
{
if (!g_mouseDownWindow)
		return;
	g_mouseDownWindow.OnMouseMove(event,true);
	event.preventDefault();
	return false;
}

function handleTouchEnd(event)
{
	if(g_mouseDownWindow){g_mouseDownWindow.OnMouseUp(event,true);event.preventDefault();}
	g_mouseDownWindow = null;
}

function handleTouchCancel(event)
{
	if(g_mouseDownWindow){g_mouseDownWindow.OnMouseUp(event,true);if(event.cancelable)event.preventDefault();}
	g_mouseDownWindow = null;
}
function handleMouseDown(event) {
	var view=event.currentTarget.ivwindow3d;
	if(view){
		view.OnMouseDown(event,false);
		g_mouseDownWindow=view;
	}
	mouseCancelPopup=false;
}


function handleMouseUp(event) {
	if(g_mouseDownWindow)g_mouseDownWindow.OnMouseUp(event,false);
	g_mouseDownWindow = null;
}

ivwindow3d.prototype.OnMouseUp=function(event,bTouch)
{

};

function GetTouchDistance(e)
{
  var dx=e.touches[0].clientX-e.touches[1].clientX;
  var dy=e.touches[0].clientY-e.touches[1].clientY;
  return Math.sqrt(dx*dx+dy*dy);
}

ivwindow3d.prototype.OnMouseDown=function(event,bTouch)
{
	var r=this.m_canvas.getBoundingClientRect();
	var e=event;
	this.lastTouchDistance=-1;
	if(bTouch)
	{
		e=event.touches[0];
		if(event.touches.length==2)
			this.lastTouchDistance=GetTouchDistance(event);
	}
	this.LX = e.clientX-r.left;
	this.LY = e.clientY-r.top;
	this.mouseMoved=false;
	var b= DecodeButtons(event,bTouch);
	if(b&4)event.preventDefault();

}

ivwindow3d.prototype.OnMouseMove=function (event,bTouch)
{
	var r=this.m_canvas.getBoundingClientRect();
	//var e=GetTouchBase(event);
	var e=event;
	if(bTouch)
	{
		e=event.touches[0];
		if(event.touches.length==2){
			var d=GetTouchDistance(event);
			if(this.lastTouchDistance!=d)
			{
				if(this.lastTouchDistance>0)
				{
					var _d=this.lastTouchDistance-d;
					
					this.handleVPFOV(_d,_d);
					this.invalidate(IV.INV_VERSION);
				}
				this.lastTouchDistance=d;
				this.mouseMoved=true;
				this.LX = e.clientX-r.left;
				this.LY = e.clientY-r.top;
			}else this.lastTouchDistance-1;
		return;
		}
	}

	var newX = e.clientX-r.left;
	var newY = e.clientY-r.top;
	var dX = newX - this.LX;
	var dY = newY - this.LY;

	if(Math.abs(dX)>1|| Math.abs(dY) || this.mouseMoved)
	{
		var b=DecodeButtons(event,bTouch);
		var invF=0;

		if(this.m_cameramode && b==1)
		{
			if(this.m_cameramode==1)b=2;else
			if(this.m_cameramode==2)b=4;
		}
		if(b&4){this.handleVPPan(dX,dY);invF=IV.INV_VERSION;}else
		if(b&1){this.handleVPRotate(dX,dY);invF=IV.INV_VERSION;}
		else
		if(b&2){
			
			if(!this.handleVPFOV(dX,dY))return;
			invF=IV.INV_VERSION;
			mouseCancelPopup=true;
		}

this.invalidate();

		this.LX = newX;
		this.LY = newY;
		this.mouseMoved=true;
	}
}

function handleMouseWheel(event)
{
	var view=event.currentTarget.ivwindow3d;
	if(view){
		var d;
		if(event.wheelDelta!=undefined)d=event.wheelDelta/-10;
		else
			if(event.detail!=undefined){
				d=event.detail;
				if(d>10)d=10;else if(d<-10)d=-10;
				d*=4;
			}

	view.handleVPDolly(0,d);
	view.invalidate(IV.INV_VERSION);
	event.preventDefault();
	}
}

ivwindow3d.prototype.handleVPPan=function(dX,dY)
{
	var gl=this.gl;
	var x0=gl.viewportWidth/2;
	var y0=gl.viewportHeight/2;
	var r0=this.GetRay(x0,y0);
	var r1=this.GetRay(x0-dX,y0-dY);
	var d=[r1[3]-r0[3],r1[4]-r0[4],r1[5]-r0[5]];
	vec3.add_ip(this.viewFrom,d);
	vec3.add_ip(this.viewUp,d);
	vec3.add_ip(this.viewTo,d);
}

ivwindow3d.prototype.handleVPRotate=function(dX,dY)
{
	var vf=this.viewFrom,t=this.viewTo.slice(),tm=[],_u=this.getUpVector(),u=this.viewUp;
	var o=vf.slice();
	vec3.normalize(_u);
	if(dX)
	{
		mat4.identity(tm);
		mat4.rotateAxisOrg(tm,t,_u,-dX/200.0);
		mat4.mulPoint(tm,vf);
		mat4.mulPoint(tm,u);
	}
	if(dY)
	{
		var _d=vec3.sub_r(t,o);
		vec3.normalize(_d);
		var _axis=vec3.cross(_d,_u,_axis);
		mat4.identity(tm);
		mat4.rotateAxisOrg(tm,t,_axis,-dY/200.0);
		mat4.mulPoint(tm,vf);
		mat4.mulPoint(tm,u);
	}
}

ivwindow3d.prototype.handleVPDolly=function(dX,dY)
{
	var dir=vec3.sub_r(this.viewFrom,this.viewTo);
	var l=vec3.length(dir);
	var _l=l+l*dY/100;
	if(_l<1e-6)return;
	vec3.normalize(dir);
	vec3.scale_ip(dir,_l);
	var _new=vec3.add_r(this.viewTo,dir);
	var delta=vec3.sub_r(_new,this.viewFrom);
	vec3.add_ip(this.viewFrom,delta);
	vec3.add_ip(this.viewUp,delta);
}

ivwindow3d.prototype.handleVPFOV=function(dX,dY)
{  
	var fov=this.fov+dY/8;
	if ( fov >= 175 )fov = 175;else
		if ( fov <= 1 )fov = 1;
	if(fov!=this.fov)
	{
		this.fov=fov;
		return true;
	}
	return false;
}


function handleContextMenu(event) {
	if(mouseCancelPopup){mouseCancelPopup=false;return false;}
	return true;
}

function handleSelectStart(event) {
	if (g_mouseDownWindow) {
		return false;
	}
	return true;
}
/*
function NewLine(space,ray)
{
	var node=space.root.NewNode();
	node.material=space.materials[0];
	var m=new mesh3d();
	node.object=m;
	var gl=space.gl;

	v=new Float32Array(6);
	v[0]=ray[0];v[1]=ray[1];v[2]=ray[2];
	v[3]=ray[3];v[4]=ray[4];v[5]=ray[5];
	m.vertexBuffer=ivBufferF(gl,v,3,2);

	var f=new Uint16Array(2);
	f[0]=0;
	f[1]=1;
	m.facesBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, m.facesBuffer);
	gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, f, gl.STATIC_DRAW);
	m.facesBuffer.itemSize = 1;
	m.facesBuffer.numItems = 2;
	m.lineMode=1;
	space.invalidate();
};
*/
ivwindow3d.prototype.getUpVector=function () {
	var _up=[this.viewUp[0]-this.viewFrom[0],this.viewUp[1]-this.viewFrom[1],this.viewUp[2]-this.viewFrom[2]];
	return _up;
}

ivwindow3d.prototype.GetRay=function(x,y,ray)
{
	var gl=this.gl;
	gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
	
	var p1=this.viewFrom;
	var p2=this.viewTo;
	var dir=vec3.sub_r(this.viewTo,this.viewFrom);
	var dirLen=vec3.length(dir);
	var up=this.getUpVector();
	
	var k=Math.tan(Math.PI*this.fov/360);

	var h2=gl.viewportHeight/2;
	var w2=gl.viewportWidth/2;
	var _k=(h2-y)/h2;
	var _kx=(x-w2)/w2;
	vec3.normalize(up);
	var xaxis=vec3.cross_rn(dir,up);
	
	var _up=vec3.scale_r(up,k*dirLen*_k);
	var _x=vec3.scale_r(xaxis,k*dirLen*_kx*gl.viewportWidth / gl.viewportHeight);

	var ray=[p1[0],p1[1],p1[2],p2[0]+_up[0]+_x[0],p2[1]+_up[1]+_x[1],p2[2]+_up[2]+_x[2]];
	return ray;
}

function DecodeButtons(event,bt)
{
	var buttons=0;
	if(bt && event.touches!=undefined)
	{
		if(event.touches.length>=3)return 4;// pan
		return 1;
	}
	if(event.buttons==undefined)
	{
		// chrome stuff
		if(event.which==1)buttons=1;
		else
		if(event.which==2)buttons=4;
		else
		if(event.which==3)buttons=2;
		else buttons=1;// just in case
	}else {
		buttons=event.buttons;// IE and Mozila
	}
	return buttons;
}

function handleMouseMove2(event)
{
	var w=event.currentTarget.ivwindow3d;
	if(w)
	{
		if (!g_mouseDownWindow){
			if(w.OnMouseHover)w.OnMouseHover(event);
		}
		else if(w==g_mouseDownWindow) w.OnMouseMove(event,false);
		else return false;
		event.stopPropagation();
	}
	return false;
}

function handleMouseMove(event) {
	if (!g_mouseDownWindow)
		return;
	g_mouseDownWindow.OnMouseMove(event,false);
	return false;
}

ivwindow3d.prototype.updateMVTM=function()
{
	mat4.lookAt(this.viewFrom, this.viewTo, this.getUpVector(),this.mvMatrix);
}

ivwindow3d.prototype.drawScene=function () {
	//this.m_repaints1++;
	var gl=this.gl;
	gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
	//bk	
	
	if(this.space.bk==undefined || (!this.space.drawBk()))
{
	var bk=this.m_color;
//$TODO$ remove work with string from production code
	var t=typeof bk;
	if( t=== 'string')
	{
		bk=parseInt(bk.substr(1,6),16);
		this.m_color=bk;
	}
	var r=((bk>>16)&0xff)/255.0;
	var g=((bk>>8)&0xff)/255.0;
	var b=(bk&0xff)/255.0;
	gl.clearColor(r,g,b,1);
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);	
}	
	this.updateMVTM();
	this.space.render(this.mvMatrix);
	this.timer=false;
}

ivwindow3d.prototype.invalidate=function(flags) {
	if(this.timer)
		return ;
	this.timer=true;
	if(flags!==undefined)
	{
		if(flags&IV.INV_VERSION)this.vpVersion++;
	}
	setTimeout(this.drawScene.bind(this),1);
}


