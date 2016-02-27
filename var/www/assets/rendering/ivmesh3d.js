function mesh3d(gl)
{ 
	this.gl=gl;
	this.vertexBuffer=null;
	this.normalBuffer=null;
	this.uvBuffer=null;
	this.facesBuffer=null;
	this.lineMode=false;
	this.url="";
	this.ref=0;
}

mesh3d.prototype.addRef = function(){this.ref++;}
mesh3d.prototype.release = function()
{
  this.ref--;
  if(this.ref<1)this.clear();
}
// generate edge list
function addEdge(e,v1,v2)
{
	if(v2>v1){var _v=v2;v2=v1;v1=_v;}//swap vertices
	if(e[v1]==undefined)e[v1]=v2;
	else
		if(typeof e[v1] === 'number')e[v1]=[e[v1],v2];
		else e[v1].push(v2);
};

mesh3d.prototype.updateEdges = function()
{
	if(!this.edgeBuffer)
	{
		var e=[];
		var f=this.faces;
		var nf=f.length/3;
		var j=0;
		var i;
		for(i=0;i<nf;i++)
		{
			addEdge(e,f[j],f[j+1]);
			addEdge(e,f[j+1],f[j+2]);
			addEdge(e,f[j+2],f[j]);
			j+=3;
		}
		var ne=e.length; 
		var num=0;
		for(i=0;i<ne;i++)
		{
			var v=e[i];
			if(v!=undefined){if(typeof v ==='number')num++;else num+=v.length;}
		}
		var edges=new Uint16Array(num*2);
		var j=0;
		for(i=0;i<ne;i++)
		{
			var v=e[i];
			if(v!=undefined)
			{
				if(typeof v==='number')
				{
					edges[j]=i;edges[j+1]=v;j+=2;
				}else
				{
					for(var i1=0;i1<v.length;i1++)
					{
						edges[j]=i;edges[j+1]=v[i1];j+=2;
					}
				}
			}
		}
		this.edgeBuffer =ivBufferI(this.gl,edges)
	}
}
function b_normalize_array(v)
{
	var sz=v.length/3;
	for(i=0;i<4;i++)
	{
		var j=i*3;
		var a=v[j],b=v[j+1],c=v[j+2];
		var l=Math.sqrt(a*a+b*b+c*c);
		if(l)
		{
			v[j]=a/l;v[j+1]=b/l;v[j+2]=c/l;
		}
	}
}

function b_getv(a,i,v)
{
	v[0]=a[i*3];
	v[1]=a[i*3+1];
	v[2]=a[i*3+2];
};

function b_gett(a,i,t)
{
	t[0]=a[i*2];
	t[1]=a[i*2+1];
};

function b_sub(a,b,l)
{
l[0]=a[0]-b[0];
l[1]=a[1]-b[1];
l[2]=a[2]-b[2];
vec3.normalize(l);
}

function b_setv(a,i,v)
{
	a[i*3]+=v[0];
	a[i*3+1]+=v[1];
	a[i*3+2]+=v[2];
};


mesh3d.prototype.updateBumpInfo = function(gl,f,v,n,uv)
{
	if(f && v && n && uv)
	{
		var wtm=mat4.create(),ttm=mat4.create(),ittm=mat4.create();
		mat4.identity(wtm);mat4.identity(ttm);
		var sz=v.length,tc=f.length;

		var a=new Float32Array(sz); 
		var b=new Float32Array(sz); 
		var i,j;
		var v0=[0,0,0],v1=[0,0,0],v2=[0,0,0];
		var t0=[0,0],t1=[0,0],t2=[0,0];
		var line0=[0,0,0],line1=[0,0,0];
		var r=[0,0,0],U=[0,0,0],V=[0,0,0];
		var vone=[0, 0, 1];
		var vzero= [0,0,0];

		for(i=0;i<tc;i++)
		{
			var i0=f[i*3],i1=f[i*3+1],i2=f[i*3+2];
			b_getv(v,i0,v0);b_getv(v,i1,v1);b_getv(v,i2,v2);
			b_gett(uv,i0,t0);b_gett(uv,i1,t1);b_gett(uv,i2,t2);
			b_sub(v0,v1,line0);
			b_sub(v1,v2,line1);
			var normal=vec3.cross_rn(line0,line1);

			for (j = 0; j < 2; j++)
			{
				var vj = (j == 0) ? v1 : v2;
				var tj = (j == 0) ? t1 : t2;
				r[0]=tj[0]-t0[0];r[1]=tj[1]-t0[1];
				vec3.normalize(r);
				mat4.setRow(ttm,j,r);
				r[0]=vj[0]-v0[0];
				r[1]=vj[1]-v0[1];
				r[2]=vj[2]-v0[2];
				vec3.normalize(r);
				mat4.setRow(wtm,j,r);
			}

			mat4.setRow(ttm, 2, vone);
			mat4.setRow(wtm, 2, normal);
			mat4.setRow(wtm, 3, vzero);
			mat4.setRow(ttm, 3, vzero);
			mat4.invert(ittm,ttm);
			//Matrix rezult = texture * world;
			mat4.m(wtm,ittm,ttm);
			U=mat4.mulVector(ttm,[1, 0, 0],U);
			V=mat4.mulVector(ttm,[0, 1, 0],V);

			b_setv(a,i0,U);b_setv(a,i1,U);b_setv(a,i2,U);
			b_setv(b,i0,V);b_setv(b,i1,V);b_setv(b,i2,V);
		}

		b_normalize_array(a);b_normalize_array(b);
		this.bnBuffer	= ivBufferF(gl,a,3);
		this.btBuffer	= ivBufferF(gl,b,3);
	}
}

function meshSetFInfo(gl,b,v){gl.bindBuffer(gl.ARRAY_BUFFER, b);gl.vertexAttribPointer(v.slot, b.itemSize, gl.FLOAT,false, 0, 0);}

mesh3d.prototype.render = function(tm,space,material,state) {
	var fb=this.facesBuffer;
	if(fb && this.vertexBuffer)
	{
		var gl=space.gl;
		if(state&16)gl.depthMask(false);

		var shFlags=8;
		var rmode=space.rmodes[(state&0xff00)>>8];
		var bEdges=rmode.e;
		if(bEdges)this.updateEdges(gl);else
		{
			if(this.normalBuffer)shFlags|=1;
			if(this.uvBuffer)shFlags|=2;
			if(this.colorBuffer)shFlags|=4;
			if(this.bnBuffer)shFlags|=16;
			
			if(state&4)shFlags|=256;
		}
		var s=space.activateMaterial(material,tm,shFlags);

		for(var i=0;i<s.attrs.length;i++)
		{
			var v=s.attrs[i];
			switch(v.id)
			{
			case "v":meshSetFInfo(gl, this.vertexBuffer,v);break;
			case "n":meshSetFInfo(gl, this.normalBuffer,v);break;
			case "uv":meshSetFInfo(gl, this.uvBuffer,v);break;
			case "bn":meshSetFInfo(gl, this.bnBuffer,v);break;
			case "bt":meshSetFInfo(gl, this.btBuffer,v);break;
		 	case "clr":gl.bindBuffer(gl.ARRAY_BUFFER, this.colorBuffer);gl.vertexAttribPointer(v.slot, this.colorBuffer.itemSize, gl.UNSIGNED_BYTE, true, 0, 0);break;
		}		
	}
	if(bEdges)
	{
		gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, this.edgeBuffer);
		gl.drawElements(gl.LINES, this.edgeBuffer.numItems, gl.UNSIGNED_SHORT, 0);
	}else{
		gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, fb);
		var o=fb.offset;
		gl.drawElements(this.lineMode?gl.LINES:gl.TRIANGLES, fb.numItems, gl.UNSIGNED_SHORT, o?o:0);
	}
	if(state&16)gl.depthMask(true);
}else
{
}
};
function ivBufferF(gl,v,cmp){
	var b = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, b);
	gl.bufferData(gl.ARRAY_BUFFER, v, gl.STATIC_DRAW);
	b.itemSize = cmp;
	b.numItems = v.length/cmp;//num;
	return b;
};

function ivBufferI(gl,v)
{
	var b = gl.createBuffer();
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, b);
	gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, v, gl.STATIC_DRAW);
	b.itemSize = 1;
	b.numItems = v.length;
return b;
}
mesh3d.prototype.clear = function()
{
var gl=this.gl;
	if(this.facesBuffer){gl.deleteBuffer(this.facesBuffer);delete this.facesBuffer;}
	if(this.uvBuffer){gl.deleteBuffer(this.uvBuffer);delete this.uvBuffer;}
	if(this.vertexBuffer){gl.deleteBuffer(this.vertexBuffer);delete this.vertexBuffer;}
	if(this.normalBuffer){gl.deleteBuffer(this.normalBuffer);delete this.normalBuffer;}
	if(this.edgeBuffer){gl.deleteBuffer(this.edgeBuffer);delete this.edgeBuffer;}
//todo Bump map buffer
}
