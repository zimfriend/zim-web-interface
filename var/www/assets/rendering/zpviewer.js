
// transform_E - transforms defined by edit controls
// transform_C - used by 3d view may be different from _E, because of limitations


function fileAMF()
{
	this.objects=[];
	this.meshes=[];
	this.scale=1.0;
}

fileAMF.prototype.parseObject=function(xml)
{
	var obj={};
	this.objects.push(obj);
	obj.meshes=[];
	var id=xml.getAttribute("id");
	if(id)obj.id=id;
	
	var items=xml.childNodes;
	var c=items.length;
	for(var i=0;i<c;i++)
	{
		var n=items[i];
		if(n.tagName)
		{
			if(n.tagName==="mesh")
			{
				var m=this.parseMesh(n);
				if(m)
				{
					this.meshes.push(m);
					obj.meshes.push(m);
				}
			}
		}
	}
};

function parseTriangle(tri,xml)
{
	if(!tri.v)tri.v=[0,0,0];
	var items=xml.childNodes;
	var c=items.length;
	for(var i=0;i<c;i++)
	{
		var n=items[i];
		if(n.tagName)
		{
			var t=n.textContent;
			switch(n.tagName)
			{
				case "v1":tri.v[0]=parseInt(t);break;
				case "v2":tri.v[1]=parseInt(t);break;
				case "v3":tri.v[2]=parseInt(t);break;
			}
		}
	}
}

function parseCoordinates(v,xml)
{
	if(!v.v)v.v=[0,0,0];
	var items=xml.childNodes;
	var c=items.length;
	for(var i=0;i<c;i++)
	{
		var n=items[i];
		if(n.tagName)
		{
			var t=n.textContent;
			switch(n.tagName)
			{
				case "x":v.v[0]=parseFloat(t);break;
				case "y":v.v[1]=parseFloat(t);break;
				case "z":v.v[2]=parseFloat(t);break;
			}
		}
	}
	return v;
};

fileAMF.prototype.parseMeshVertices=function(mesh,xml)
{
	if(!mesh.vertices)mesh.vertices=[];
	var items=xml.childNodes;
	var c=items.length;
	for(var i=0;i<c;i++)
	{
		var n=items[i];
		if(n.tagName)
		{
			if(n.tagName=="vertex")
			{
				var _items=n.childNodes;
				if(_items)
				{
					
					for(var j=0;j<_items.length;j++)
					{
						var _n=_items[j];
						if(_n.tagName && _n.tagName=="coordinates")
						{
							var v={};
							parseCoordinates(v,_n);
							mesh.vertices.push(v);
						}
					}
				}
			}
		}
	}
};



fileAMF.prototype.parseMeshVolume=function(mesh,xml)
{
	if(!mesh.volumes)mesh.volumes=[];
	var v={};
	mesh.volumes.push(v);
	var items=xml.childNodes;
	var c=items.length;
	v.triangles=[];
	for(var i=0;i<c;i++)
	{
		var n=items[i];
		if(n.tagName)
		{
			if(n.tagName=="triangle")
			{
				var t={};
				parseTriangle(t,n);
				v.triangles.push(t);
			}
		}
	}
}

fileAMF.prototype.parseMesh=function(xml)
{
	var mesh={};
	mesh={};
	var items=xml.childNodes;
	var c=items.length;
	var nodes=[];
	for(var i=0;i<c;i++)
	{
		var n=items[i];
		if(n.tagName)
		{
			if(n.tagName==="vertices"){this.parseMeshVertices(mesh,n);nodes.push(n);}else
			if(n.tagName==="volume"){this.parseMeshVolume(mesh,n);nodes.push(n);}
		}
	}
	while(nodes.length)
	{
		var n=nodes.pop();
		xml.removeChild(n);
	}

	if(mesh.vertices && mesh.volumes)return mesh;
	return mesh;
}


function assignNormals(tr)
{	
	var c=tr.length
	var smoothV=[],i;
	for(i=0;i<c;i++)
	{
		var t=tr[i];
		t.nv=[0,0,0];
		for(var j=0;j<3;j++)
		{
			var iv=t.v[j];
			//var _v=v[];
			var si={f:i,v:j};
			if(smoothV[iv])si.n=smoothV[iv];
			smoothV[iv]=si;
		}
	}
	var cosa=Math.cos(Math.PI*35/180.0);
	var _nv_count=0;
	var vc=smoothV.length;
	for(i=0;i<vc;i++)
	{
		var _p1=smoothV[i];
		while(_p1)
		{
			_p2=_p1.n;
			while(_p2)
			{
				if(!_p1.p || !_p2.p)
				{
					var a=vec3.dot(tr[_p1.f].n,tr[_p2.f].n);
					if(a>=cosa)
					{
						if(!_p2.p)_p2.p=_p1;else _p1.p=_p2;
					}
				}
				_p2=_p2.n;
			}
			_p1=_p1.n;
		}
		_p1=smoothV[i];
		while(_p1){
			_p2=_p1.p;
			if(_p2){while(_p2.p)_p2=_p2.p;}
			_p1.p=_p2;
			if(!_p2)_nv_count++;
			_p1=_p1.n;
		}
	}

	var k=0;
	for(i=0;i<vc;i++)
	{
		var _p1=smoothV[i];
		while(_p1){
			if(!_p1.p){
				var _p2=_p1.n;
				tr[_p1.f].nv[_p1.v]=k;
				while(_p2){
					if(_p2.p==_p1){
						tr[_p2.f].nv[_p2.v]=k;
					}
					_p2=_p2.n;
				}
				k++;
			}
			_p1=_p1.n;
		}
		//smoothV[i]=null;
	}
	//delete smoothV;
	return _nv_count;
}

function calcTriangleNormals(v,tr)
{
	var c=tr.length
	var e1=[],e2=[];
	for(var i=0;i<c;i++)
	{
		var t=tr[i];
		var _p1=t.v;
		var a=v[_p1[0]].v;
		vec3.subtract(v[_p1[1]].v,a,e1);
		vec3.subtract(v[_p1[2]].v,a,e2);
		t.n=vec3.cross_rn(e1,e2);
		// we have per triangle normal
	}
};

function generateNormals(v,tr)
{
	var c=tr.length
	
	var vc=v.length,i;
	for(i=0;i<vc;i++)
	{
		if(v[i].n)
			v[i].n=null;
	}
	calcTriangleNormals(v,tr);
	_nv_count=assignNormals(tr);
	var vc3=_nv_count*3;
	var N=new Float32Array(vc3);

	for(i=0;i<c;i++)
	{
		var t=tr[i];
		if(t.nv){
			var tn=t.n;
			var x=tn[0],y=tn[1],z=tn[2];
			tn=t.nv;
			for(j=0;j<3;j++)
			{
				var ni=tn[j]*3;
				N[ni]+=x;
				N[ni+1]+=y;
				N[ni+2]+=z;
			}
		}
	}
	for(i=0;i<vc3;i+=3)
	{
		var x=N[i],y=N[i+1],z=N[i+2];
		var g=Math.sqrt(x*x+y*y+z*z);
		if(g && g!=1)
		{
			N[i]=x/g;N[i+1]=y/g;N[i+2]=z/g;
		}
	}
	return N;

};

function calcMeshBBox(m)
{
	var V=m.points;
	var c=V.length/3;
	var vminx=V[0],vminy=V[1],vminz=V[2];
	var vmaxx=vminx,vmaxy=vminy,vmaxz=vminz;
	for(var i=1;i<c;i++)
	{
	  var j=i*3;
	  var d=V[j];
	  if(d<vminx)vminx=d;else if(d>vmaxx)vmaxx=d;
	  d=V[j+1];
	  if(d<vminy)vminy=d;else if(d>vmaxy)vmaxy=d;
	  d=V[j+2];
	  if(d<vminz)vminz=d;else if(d>vmaxz)vmaxz=d;
	}
	m.boxMin=[vminx,vminy,vminz];
	m.boxMax=[vmaxx,vmaxy,vmaxz];
}

function makeMesh(m,v,tr,normals)
{
	var c=normals.length/3;
	m.normalBuffer = ivBufferF(m.gl,normals,3);
	var tc=tr.length;
	var f=new Uint16Array(tc*3);
	V=normals;
	j=0;
	for(i=0;i<tc;i++)
	{
		var t=tr[i];
		var nv=t.nv;
		for(var k=0;k<3;k++)
		{
			var vi=nv[k];
			//if(vi>=c)		vi=0;
			f[j]=vi;j++;// optimize that
			var vv=v[t.v[k]].v;
			V[vi*3]=	vv[0];
			V[vi*3+1]=	vv[1];
			V[vi*3+2]=	vv[2];
		}
	}

	m.vertexBuffer = ivBufferF(m.gl,V,3);
	m.points=V;
	calcMeshBBox(m);
	m.facesBuffer =ivBufferI(m.gl,f);
};


function finalizeMesh(iFrom,iTo,info)
{
	var m=new mesh3d(info.gl);
	var n=info.node.newNode();
	n.material=info.mtl;
	n.setObject(m);
	var d=info.data;
	var c=d.length/2;
	var r=info.r;
	var f=new Uint16Array((iTo-iFrom+1)*3);
	var j=0;
	var tr=info.tr;
	for(var iT=iFrom;iT<=iTo;iT++)
	{
		var t=tr[iT];
		var nv=t.nv;
		for(var k=0;k<3;k++)
		{
			f[j]=r[nv[k]]-1;j++;
		}
		tr[iT]=null;// system can free this memory
	}
	m.facesBuffer =ivBufferI(m.gl,f);
	delete f;

	var vc3=c*3;
	var V=new Float32Array(vc3);
	j=0;
	var n=info.n;
	for(var i=0;i<vc3;i+=3)
	{
		var ni=d[j];
		ni*=3;
		V[i]=n[ni];V[i+1]=n[ni+1];V[i+2]=n[ni+2];
		j+=2;
	}
	m.normalBuffer = ivBufferF(info.gl,V,3);
	j=1;
	var v=info.v;
	for(var i=0;i<vc3;i+=3)
	{
		var _v=v[d[j]].v;
		V[i]=_v[0];V[i+1]=_v[1];V[i+2]=_v[2];
		j+=2;
	}
	m.points=V;
	m.vertexBuffer = ivBufferF(m.gl,V,3);
	calcMeshBBox(m);
};

function makeMeshSubD(gl,node,mtl,v,tr,normals)
{
	var info={};
	var sz=normals.length/3;
	var r=new Uint16Array(sz);
	var data=[];
	info.data=data;
	info.r=r;
	info.v=v;
	info.tr=tr;
	info.n=normals;
	info.mtl=mtl;
	info.gl=gl;
	info.node=node;

	var tc=tr.length;
	var count=0;
	var _iT=0;
	for(var iT=0;iT<tc;iT++)
	{
		var t=tr[iT];
		var nv=t.nv;
		for(var k=0;k<3;k++)
		{
			var ni=nv[k];
			var j;
			var ref=r[ni];
			if(ref)j=ref-1;
			else
			{	
				count++;
				r[ni]=count;
				data.push(ni);
				data.push(t.v[k]);
			}
		}
		if(count>65530)
		{
			finalizeMesh(_iT,iT,info);
			for(var i=0;i<sz;i++)r[i]=0;
			_iT=iT+1;
			info.data=data=[];
			count=0;
		}
	}
	if(_iT<tc)
		finalizeMesh(_iT,tc-1,info);
};


fileAMF.prototype.makeScene=function(view)
{
	var scene=view.space;
	var root=scene.root.newNode();
	view.amfObject=root;
	var mtl1=view.fileMaterial1;
	var mtl2=view.fileMaterial2;
	scene.materials.push(mtl1);
	scene.materials.push(mtl2);

	for(var iMesh=0;iMesh<this.meshes.length;iMesh++)
	{
		var mesh=this.meshes[iMesh];
		for(var i=0;i<mesh.volumes.length;i++)
		{
			var volume=mesh.volumes[i];
			var normals=generateNormals(mesh.vertices,volume.triangles);
			var n=root.newNode();

			//DIY crossover material for several parts
			// var mtl=i?mtl2:mtl1;
			var mtl = (i%2) ? mtl2 : mtl1;
			//DIY end - PNI
			if((normals.length/3)<65535)
			{
				var m=new mesh3d(scene.gl);
				makeMesh(m,mesh.vertices,volume.triangles,normals);
				n.setObject(m);
				n.material=mtl;
			}else
			{
				makeMeshSubD(scene.gl,n,mtl,mesh.vertices,volume.triangles,normals);
			}
			if(this.scale!=1.0)
			{
				n.enableTM();
				mat4.setScale(n.tm,this.scale);
			}

			
		}
	}
	//view.amfScaleBase=this.scale;
	postCreateObject(view);
}


function postCreateObject(view)
{
	var scene=view.space;
	var root=view.amfObject;
	view.amfBBox=root.getBoundingBox(null,null);
	
	this.setInitialScale(view);
	var node=scene.root.getNodeById("wait");
	if(node)node.state&=~3;// hide wait cursor

	onPosChanged();
	updateDimensions(view.amfBBox,view.transform_C.scale);
}

function setInitialScale(view)
{
	// default scale
	var sx=view.amfBBox[3]-view.amfBBox[0];
	var sy=view.amfBBox[4]-view.amfBBox[1];
	var sz=view.amfBBox[5]-view.amfBBox[2];
	var kx=sx/view.paneX;
	var ky=sy/view.paneY;
	var kz=sz/view.paneZ;
	if(ky>kx)kx=ky;
	if(kz>kx)kx=kz;
	var _kx=100/kx;
	if(kx>1)
	{
		view.transform_0.scale=_kx;
		view.transform_E.scale=_kx;
		view.transform_C.scale=_kx;
	}
	var maxScale=_kx*2;
	var slider=document.getElementById("slicer_size");
	maxScale=Math.ceil(maxScale/10)*10;
	thumb=null;
	 //DIY enable UI change only in pre-slicing case, not in post-slicing case
	if(slider){
		slider.max=maxScale;
		$("input#slicer_size").slider("refresh");
	}
	// updateUIFolder();
	//DIY end - PNI
}


function ParseAMF(xml)
{
	if(xml && xml.childNodes.length)
	{
		var amf=xml.childNodes[0];
		if(amf && amf.tagName && amf.tagName=="amf" && amf.childNodes)
		{
			var file=new fileAMF();

			var id=amf.getAttribute("unit");
			if(id)
			{
				if(id=="millimeter")file.scale=0.1;
			}

			var items=amf.childNodes;
			var c=items.length;
			for(var i=0;i<c;i++)
			{
				var n=items[i];
				if(n.tagName){
					if(n.tagName==="object")file.parseObject(n);
				}
			}
			if(file.meshes.length)
			{
				file.makeScene(view3d);
			}
		}
	}
}

function zpLoadModel(view,file)
{
	var path;
	var request = CreateRequest(file,path);
	
	//DIY timeout
	request.timeout = 120000;
	request.ontimeout = function() {alert("timeout");}
	//DIY end - PNI
	request.ivwnd=view;
	//DIY make difference between STL and AMF by JS variable (AMF as default)
	request.isAMF = (typeof(var_multi_part) == 'undefined' || var_multi_part == true) ? true : false;
	//DIY end - PNI
	request.onreadystatechange = function () {
		//DIY trigger file size or internal error rollback
		if (this.readyState == 4) {
			if (this.status==200) {
				if (this.isAMF == true) {
					ParseAMF(this.responseXML);
				}
				else {
					var arrayBuffer = this.response;
					STLParse(this.ivwnd,this.response);
				}
				
				if (typeof(onWebGL_finalized) == 'function') {
					//add to trigger a coordinates' setting event
					onWebGL_finalized();
				}
			}
			else {
				// alert("trigger rollback");
				if (typeof(onWebGLRequest_rollback) == 'function') {
					onWebGLRequest_rollback();
				}
			}
		}
		//DIY end - PNI
	}
	if (request.isAMF == true) {
		request.overrideMimeType("text/xml");
	}
	else {
		request.responseType = "arraybuffer"; //binary
	}
	request.send();
}

mat4.setScale=function(tm,s)
{
	tm[10]=tm[5]=tm[0]=s;
}

function AMFTransform()
{
	this.rx=this.ry=this.rz=0;
	this.scale=100;
}

AMFTransform.prototype.copyTo=function(t)
{
	t.rx=this.rx;
	t.ry=this.ry;
	t.rz=this.rz;
	t.scale=this.scale;
}


function onPosChanged(value)
{
	if(view3d.amfObject)
	{
		view3d.amfObject.enableTM();
		view3d.calcObjTM(view3d.transform_C,view3d.amfObject.tm);
		view3d.invalidate();
	}
}

ivwindow3d.prototype.calcObjTM=function(d,tm)
{
	mat4.identity(tm);
	var box=this.amfBBox;
	if(box)
	{
		mat4.identity(tm);
		for(var j=0;j<3;j++)tm[12+j]=-(box[j]+box[j+3])/2;
		if(d.scale!=100)
		{
			var _s=[];
			mat4.identity(_s);
			mat4.setScale(_s,d.scale/100);
			mat4.m(tm,_s);
		}
		//mat4.setRor(3,[-(box[0]+[box[3])/2,]);
	}else
		mat4.setScale(tm,d.scale/100);

	if(d.rx)mat4.rotateX(tm,Math.PI*d.rx/180.0);
	if(d.ry)mat4.rotateY(tm,Math.PI*d.ry/180.0);
	if(d.rz)mat4.rotateZ(tm,Math.PI*d.rz/180.0);
	var b=this.amfObject.getBoundingBox(tm,null);
	if(b)
	{
		tm[14]+=-b[2];//+0.15 - possible offset
		//tm[14]-=7.5;
	}
	var sx=b[3]-b[0];
	var sy=b[4]-b[1];
	var sz=b[5]-b[2];
	
	if((this.paneX>=sx)&&(this.paneY>=sy)&&(this.paneZ>=sz))
	{
		updateDimensions(b,100);
		return true;
	};
	return false;
}

function updateDimensions(box,scale)
{
	scale/=100;
	var x=(box[3]-box[0])*scale;
	var y=(box[4]-box[1])*scale;
	var z=(box[5]-box[2])*scale;
	x=Math.round(x*100)/10;
	y=Math.round(y*100)/10;
	z=Math.round(z*100)/10;
	$("span#model_xsize_info").html(x);
	$("span#model_ysize_info").html(y);
	$("span#model_zsize_info").html(z);
};

ivwindow3d.prototype.cm_reset=function()
{
	this.transform_0.copyTo(this.transform_E);
	this.transform_0.copyTo(this.transform_C);
	onPosChanged();
	updateUIFolder();
}

//DIY custom api call function
/* 
function onSliderChanged(cnt)
{
	var v=cnt.value;
	switch(cnt.id)
	{
		case "slicer_size":view3d.transform_E.scale=v;break;
		case "slicer_rotate_x":view3d.transform_E.rx=v;break;
		case "slicer_rotate_y":view3d.transform_E.ry=v;break;
		case "slicer_rotate_z":view3d.transform_E.rz=v;break;
		default:return;
	}
	onEditChanged()
}
 */
function onSliderChanged(t, v)
{
	switch(t)
	{
		case "s":view3d.transform_E.scale=v;break;
		case "x":view3d.transform_E.rx=v;break;
		case "y":view3d.transform_E.ry=v;break;
		case "z":view3d.transform_E.rz=v-180;break;
		default:return;
	}
	onEditChanged()
}
//DIY end - PNI

function setToSlider(name,v)
{
	var slider=document.getElementById(name);
	slider.value=Math.round(v*100)/100;
	// update bootstrap slider properly
}

function updateUIFolder()
{
	var d=view3d.transform_E;
	setToSlider("slicer_size",d.scale);
	setToSlider("slicer_rotate_x",d.rx);
	setToSlider("slicer_rotate_y",d.ry);
	setToSlider("slicer_rotate_z",d.rz);
	//DIY refresh slider
	$("input#slicer_rotate_x").slider("refresh");
	$("input#slicer_rotate_y").slider("refresh");
	$("input#slicer_rotate_z").slider("refresh");
	$("input#slicer_size").slider("refresh");
	//DIY end - PNI
}


function onEditChanged()
{
	var tm=[];
	var bOk=view3d.calcObjTM(view3d.transform_E,tm);
	if(bOk)
	{
		view3d.amfObject.enableTM();
		view3d.transform_E.copyTo(view3d.transform_C);
		mat4.copy(tm,view3d.amfObject.tm);
		view3d.invalidate();
	}
}


function ivMyhandleVPRotate(dX,dY)
{
	var vf=this.viewFrom,t=this.viewTo.slice(),tm=[],u=this.viewUp;
	var o=vf.slice();
	

	if(dY)
	{
		var _u=this.getUpVector();
		vec3.normalize(_u);
		var _d=vec3.sub_r(t,o);
		vec3.normalize(_d);
		var _axis=vec3.cross(_d,_u,_axis);
		mat4.identity(tm);
		mat4.rotateAxisOrg(tm,t,_axis,-dY/200.0);
		mat4.mulPoint(tm,vf);
		mat4.mulPoint(tm,u);
	}
	if(dX)
	{
		var _u=[0,0,1];
		mat4.identity(tm);
		mat4.rotateAxisOrg(tm,t,_u,-dX/200.0);
		mat4.mulPoint(tm,vf);
		mat4.mulPoint(tm,u);
	}
}

function onResize3D()
{
	var cnv=view3d.m_canvas;
	var p=cnv.parentNode;
	//cnv.height = h;
	var h=cnv.height;
	var w=p.clientWidth;
	//DIY add a border to improve mobile pageup/pagedown usage
	// if (document.body.clientHeight < 550) { // leave a bar of (550 - 505)px to operate
		w = w * 0.9;
	// }
	//DIY end - PNI
	if(w){
	cnv.width = w;
	if(view3d.gl){
	view3d.gl.viewportHeight = h;
	view3d.gl.viewportWidth = w;
	view3d.invalidate();
	}}
}


function zponDataReady(space)
{
	onResize3D();

	var w=space.window;
	w.setDefView();
	w.handleVPRotate(100,0);// rotate left/right
	w.handleVPRotate(0,60);// rotate up/down

	// keep changed data - 
	var v=space.view;
	v.org=w.viewFrom.slice();
	v.target=w.viewTo.slice();
	v.up=w.viewUp.slice();
	
	//DIY change function name to support STL and AMF
	// zpLoadAMF(w,w.fileToLoad);
	zpLoadModel(w, w.fileToLoad);
	//DIY end - PNI
}

// IOS antialiasing
function frame3d(gl)
{
	this.rttFb=gl.createFramebuffer();//framebuffer
	this.rttTxt=null;
	gl.bindFramebuffer(gl.FRAMEBUFFER, this.rttFb);
	this.ky=this.kx=2;
	this.rttFb.width=gl.viewportWidth*this.kx;
	this.rttFb.height=gl.viewportHeight*this.ky;
	this.rttTxt = gl.createTexture();
	gl.bindTexture(gl.TEXTURE_2D, this.rttTxt);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);

	gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, this.rttFb.width, this.rttFb.height, 0, gl.RGBA, gl.UNSIGNED_BYTE, null);
	this.renderbuffer = gl.createRenderbuffer();
	gl.bindRenderbuffer(gl.RENDERBUFFER, this.renderbuffer);
	gl.renderbufferStorage(gl.RENDERBUFFER, gl.DEPTH_COMPONENT16, this.rttFb.width, this.rttFb.height);

	

	gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, this.rttTxt, 0);
	gl.framebufferRenderbuffer(gl.FRAMEBUFFER, gl.DEPTH_ATTACHMENT, gl.RENDERBUFFER, this.renderbuffer);
	gl.bindTexture(gl.TEXTURE_2D, null);
	gl.bindRenderbuffer(gl.RENDERBUFFER, null);
	gl.bindFramebuffer(gl.FRAMEBUFFER, null);

	this.uv=new Float32Array([0,0,1,0,0,1,0,1,1,0,1,1]);
	this.uvBuffer=ivBufferF(gl,this.uv,2);
	this.vertexBuffer=ivBufferF(gl,new Float32Array([-1.0,-1.0, 0.0,1.0, -1.0, 0.0,-1.0,1.0, 0.0,-1.0,1.0, 0.0,1.0,-1.0, 0.0,1.0,1.0,0.0]),3);

	
	var vText="attribute vec3 inV;attribute vec2 inUV;varying vec3 v;varying vec2 uv;void main(void){v=inV;uv=inUV*vec2("+1.0+","+1.0+");gl_Position = vec4(inV,1.0);}";
	var fText="precision mediump float;\r\nvarying vec3 v;varying vec2 uv;uniform vec2 offset;\r\nuniform sampler2D txt;\r\nvoid main(void) ";

	fText+="{vec4 txtColor= texture2D(txt,uv)+texture2D(txt,uv+offset)+texture2D(txt,vec2(uv.x,uv.y+offset.y))+texture2D(txt,vec2(uv.x+offset.x,uv.y));txtColor/=4.0;"
	fText+="gl_FragColor=txtColor;}";

	this.vShader = ivCompileShader(gl, vText,gl.VERTEX_SHADER);
	this.fShader = ivCompileShader(gl, fText,gl.FRAGMENT_SHADER);

	var shPrg = gl.createProgram();
	this.program=shPrg;
	gl.attachShader(shPrg, this.vShader);
	gl.attachShader(shPrg, this.fShader);
	gl.linkProgram(shPrg);

	if (!gl.getProgramParameter(shPrg, gl.LINK_STATUS)) {
		alert("Could not initialise shaders");
	}
	gl.useProgram(shPrg);

	//
	this.slotUV=gl.getAttribLocation(this.program, "inUV");
	this.slotV=gl.getAttribLocation(this.program, "inV");
	this.txtUniform= gl.getUniformLocation(shPrg, "txt");
	this.offsetUniform=gl.getUniformLocation(shPrg, "offset");
	gl.useProgram(null);
}

frame3d.prototype.restore = function(gl)
{
	gl.bindFramebuffer(gl.FRAMEBUFFER, null);
}
frame3d.prototype.activate = function(gl)
{
	gl.bindFramebuffer(gl.FRAMEBUFFER, this.rttFb);
}


function ivWindow3dMydrawScene(){

	var gl=this.gl;
	this.frame.activate(gl);
	//gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
	var f=this.frame;
	gl.viewport(0, 0, f.rttFb.width, f.rttFb.height);
	
	// gl.clearColor(1,1,1,1);
	gl.clearColor(0.875,0.875,0.875,1);
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

	mat4.lookAt(this.viewFrom, this.viewTo, this.getUpVector(),this.mvMatrix);
	this.space.render(this.mvMatrix);
	this.frame.restore(gl);
	
	gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
	gl.clear(gl.DEPTH_BUFFER_BIT);
	
	gl.useProgram(f.program);
	gl.enableVertexAttribArray(0);
	gl.bindBuffer(gl.ARRAY_BUFFER, f.vertexBuffer);
	gl.vertexAttribPointer(f.slotV, f.vertexBuffer.itemSize, gl.FLOAT, false, 0, 0);
	
	gl.enableVertexAttribArray(1);
	gl.bindBuffer(gl.ARRAY_BUFFER, f.uvBuffer);
	gl.vertexAttribPointer(f.slotUV, f.uvBuffer.itemSize, gl.FLOAT, false, 0, 0);

	gl.activeTexture(gl.TEXTURE0);
	gl.bindTexture(gl.TEXTURE_2D, f.rttTxt);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
	gl.uniform1i(f.txtUniform,0);
	var k=1.0;
	gl.uniform2f(f.offsetUniform,k/(f.rttFb.width),k/(f.rttFb.height));
	//gl.uniform2f(f.offsetUniform,0,0);

	gl.disable(gl.DEPTH_TEST);
	gl.depthMask(false);
	gl.drawArrays(gl.TRIANGLES, 0, 6);

	gl.disableVertexAttribArray(1);
	gl.disableVertexAttribArray(0);
	gl.activeTexture(gl.TEXTURE0);
	gl.bindTexture(gl.TEXTURE_2D, null);

	gl.enable(gl.DEPTH_TEST);
	gl.useProgram(null);
	gl.depthMask(true);

	this.timer=false;
}

function hex2rgb(hex) {
	var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	// in principle, we need to / 255, but we just use 64 for interface
	return result ? {
		r: parseInt(result[1], 16)/64,
		g: parseInt(result[2], 16)/64,
		b: parseInt(result[3], 16)/64
	} : null;
}

function exchangeRenderColor(channelName) {
	var tmpChannel1;
	var tmpChannel2;
	var tmpColor;
	
	tmpChannel1 = view3d.fileMaterial1.getChannel(channelName);
	tmpChannel2 = view3d.fileMaterial2.getChannel(channelName);
	if (tmpChannel1 && tmpChannel2) {
		tmpColor = tmpChannel1.color;
		tmpChannel1.color = tmpChannel2.color;
		tmpChannel2.color = tmpColor;
	}
}

function exchangeRenderColors() {
	exchangeRenderColor("diffuse");
	exchangeRenderColor("specular");
	exchangeRenderColor("emissive");
	
	view3d.invalidate();
}

// Init 3D View
// cnv - canvas, file URL to AMF file
function zpInit3d(cnv,file,color1,color2)
{
	// background color 0xe0e0e0, "view" in "tree.json" controls camera (from, to, up system)
	var view=new ivwindow3d(cnv,"tree.json",0xe0e0e0,"/assets/rendering/platform/");
	if(view.m_bOk)
	{
		var attr=view.gl.getContextAttributes();
		if(!attr.antialias)
		{
			view.frame=new frame3d(view.gl);
			view.drawScene=ivWindow3dMydrawScene;
		}

		view.onDataReady=zponDataReady;
		view3d=view;
		window.onresize = onResize3D;
		view.space.cfgDbl=false;
		view.transform_0=new AMFTransform();
		view.transform_E=new AMFTransform();
		view.transform_C=new AMFTransform();
		view.paneX=15;
		view.paneY=14.5; //DIY original 15 - PNI
		view.paneZ=15;
		view.handleVPRotate=ivMyhandleVPRotate;
		//DIY ignore no file parameter passing case
		// if(!file)file="horseshoe.amf";
		if (file) {
			view.fileToLoad=file;
			var mtl=new material3d(view.space);
			// mtl.load({"diffuse":{"color":[0.3,0.3,0.3]},"specular":{"color":[0.7,0.7,0.7]},"emissive":{"color":[0.1,0.1,0.1]},"phong":16});
			var rgb = hex2rgb(color1);
			if (rgb != null) {
				mtl.load({"diffuse":{"color":[0.3*rgb.r,0.3*rgb.g,0.3*rgb.b]},"specular":{"color":[0.7*rgb.r,0.7*rgb.g,0.7*rgb.b]},"emissive":{"color":[0.1*rgb.r,0.1*rgb.g,0.1*rgb.b]},"phong":16});
			}
			else {
				mtl.load({"diffuse":{"color":[0.3,0.3,0.3]},"specular":{"color":[0.7,0.7,0.7]},"emissive":{"color":[0.1,0.1,0.1]},"phong":16});
			}
			view.fileMaterial1=mtl;
			mtl=new material3d(view.space);
			// mtl.load({"diffuse":{"color":[1.0,0.3,0.3]},"specular":{"color":[0.8,0.7,0.7]},"emissive":{"color":[0.2,0.1,0.1]},"phong":16});
			rgb = hex2rgb(color2);
			if (rgb != null) {
				mtl.load({"diffuse":{"color":[0.3*rgb.r,0.3*rgb.g,0.3*rgb.b]},"specular":{"color":[0.7*rgb.r,0.7*rgb.g,0.7*rgb.b]},"emissive":{"color":[0.1*rgb.r,0.1*rgb.g,0.1*rgb.b]},"phong":16});
			}
			else {
				mtl.load({"diffuse":{"color":[0.6,0.6,0.6]},"specular":{"color":[0.9,0.9,0.9]},"emissive":{"color":[0.3,0.3,0.3]},"phong":16});
			}
			view.fileMaterial2=mtl;
		}
		//DIY end - PNI
		onResize3D();
		return view;
	}
	//DIY assign webgl detection
	else if (typeof(var_webgl_support) != 'undefined') {
		var_webgl_support = false;
	}
	//DIY end - PNI
	return null;
}


