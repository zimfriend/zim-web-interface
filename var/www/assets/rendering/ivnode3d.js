/*
	node state
	1 - visible thsi
	2 - visible children
	4 - selection
	8 - closed
	16 - no z write
	32 - double sided - force double sided
	64 - no hit testing
*/

function node3d()
{
	this.object=null;
	this.tm=null;
	this.name="";
	this.material=null;
	this.state=3;
	this.ref=0;
}

node3d.prototype.addRef = function(){this.ref++;}
node3d.prototype.release = function()
{
  this.ref--;
  if(this.ref<1)this.clear();
}

node3d.prototype.newNode = function()
{
	var n=new node3d();
	this.insert(n);
	return n;
}
node3d.prototype.insert = function(n)
{       n.ref++;
	if(this.lastChild)this.lastChild.next=n;
	else
		this.firstChild=n;
	this.lastChild=n;
	n.parent=this;
}
node3d.prototype.clear = function()
{
  while(this.firstChild)this.remove(this.firstChild);
  this.setObject(null);
}
node3d.prototype.remove = function(n)
{
	if(n.parent!=this)return false;
	var _n=null;
	if(this.firstChild==n)
	{
		this.firstChild=n.next;
	}else
	{
		_n=this.firstChild;
		while(_n)
		{
			if(_n.next==n)
			{
				_n.next=n.next;
				break;
			}
			_n=_n.next;
		}
	}
	if(this.lastChild==n)
			this.lastChild=_n;
	n.parent=null;
	n.next=null;
	n.release();
	return true;
}

node3d.prototype.setState = function(s,mask)
{
	var _state=this.state& (~mask)| mask&s;
	if(_state!=this.state)
	{
		this.state=_state;
		return true;
	}
	return false;
}

node3d.prototype.traverse = function(ptm,proc,param,astate) {
	astate|=(this.state&4);//selection
	if(this.state&0xff00){astate&=~0xff00;astate|=this.state&0xff00;}//render mode
	var v=3;
	{v=this.state&3;if(!v)return;}
	var newtm;
	if(this.tm)
	{
		newtm = mat4.create();
		mat4.m(this.tm,ptm,newtm);
	}else newtm=ptm;
if(v&1){
	if(!proc(this,newtm,param,astate))return ;
 }

 if(v&2){
	var child=this.firstChild;
	while(child)
	{
		child.traverse(newtm,proc,param,astate);
		child=child.next;
	}
}
};

node3d.prototype.setObject = function(obj)
{
if(this.object!=obj)
{
  if(this.object)this.object.release();
  this.object=obj;
  if(obj)obj.ref++;
}
}
node3d.prototype.load = function(d,info)
{
	if(d.name!==undefined)this.name=d.name;
	if(d.meta!==undefined)this.meta=d.meta;
	if(d.object!=undefined)
		this.setObject(info.objects[d.object]);
	if(d.mtl!=undefined){
		this.material=info.materials[d.mtl];
		if(this.material && this.material.bump && this.object)this.object.bump=true;// generate bump tangents
	}

	if(d.s!=undefined)this.state= d.s;
	if(d.t!=undefined)this.type= d.t;
	if(d.tm)
	{
		this.tm=mat4.create();
		mat4.identity(this.tm);
		var index=0; 
		for(i=0;i<4;i++)
		{
			for(j=0;j<3;j++)
			{
				this.tm[i*4+j]= d.tm[index];
				index++;
			}
		}
	}
	if(d.i)
	{
		var n=d.i;
		for(var i=0;i<n.length;i++)
		{
			var node=this.newNode();
			node.load(n[i],info);
		}
	}
}

function nodeRender(node,tm,space,state){
	var o=node.object;
	if(o){
		if(o.url){
			var r = CreateRequest(o.url);
			if(r){
				space.meshesInQueue++;
				r.ivobject=o;
				r.ivspace=space;
				loadMesh(r);
				r.send();}
			delete o.url;
		}
		else{
			if(o.boxMin)space.toRenderQueue(tm,node,state);
			}
	}
	return true;
};

node3d.prototype.GetWTM=function(){
	var tm=null;
	var n=this;
	while(n)
	{
		if(n.tm)
		{
			if(tm)
			{
				mat4.m(tm,n.tm);
			}else tm=mat4.create(n.tm);
		}
		n=n.parent;
	}
	return tm;
};

;function loadMesh(request)
{
	request.responseType = "arraybuffer";//binary
	request.onreadystatechange = function () {
		if (this.readyState == 4 && this.status==200) {// this not request here
			var arrayBuffer = this.response;
			this.ivobject.onMeshReady(this.ivspace,arrayBuffer );
			this.ivspace.onMeshLoaded(this.ivobject);
		}
	}
}
;function ivBitStream(dv,dvpos)
{
	this.st=dv;
	this.stpos=dvpos;
	this.m_b=0;
	this.m_pos=0;
};
ivBitStream.prototype.Read = function(b)
{
	var r=0;
	while(b)
	{
		if(!this.m_pos){this.m_b=this.st.getUint8(this.stpos++);this.m_pos=8;}
		var t=b;
		if(t>this.m_pos)t=this.m_pos;
		r<<=t;
		r|=(this.m_b&0x0ff)>>(8-t);
		this.m_b<<=t;
		b-=t;
		this.m_pos-=t;
	}
	return r;
}

function NumBits(index)
{
	var i=0;
	while(index){index>>=1;i++;}
	return i;
}

function ivdcmpf(cmp,v,c)
{
	var index=0,k=0,code=0;
	while(k<c)
	{
		code=cmp.Read(2);
		if(code==0){v[k]=index;index++;}else
		{
			code<<=1;
			code|=cmp.Read(1);
			if(code==4)v[k]=v[k-1]+1;else
			if(code==5)v[k]=v[k-cmp.Read(4)-2];else
			if(code==6)v[k]=cmp.Read(NumBits(index));else
			if(code==7)//op_delta
			{
				var sign=cmp.Read(1);
				var delta=cmp.Read(5);
				if(sign)delta*=-1;
				v[k]=v[k-1]+delta;
			}else
			{
				var j=k-(cmp.Read(code==2?4:13)+1);
				var _v1,_v2;
				if(j%3 || (j>=(k-2))){_v1=j;_v2=j-1;}else{_v2=j+2;_v1=j;}
				v[k]=v[_v1];
				v[k+1]=v[_v2];
				k++;
			}
		}
		k++;
	}
};
function dcdnrml(d,i,n,j)
{
	var nx,ny,nz,k,l;
	var a=9.5875262183254544e-005*d.getUint16(i,true);
	var b=4.7937631091627272e-005*d.getUint16(i+2,true);
	k=Math.sin(b);
	nx=Math.cos(a)*k;
	ny=Math.sin(a)*k;
	nz=Math.cos(b);
	l=Math.sqrt(nx*nx+ny*ny+nz*nz);//normalize, in order to avoid rounding 
	nx/=l;ny/=l;nz/=l;
	n[j]=nx;j++;n[j]=ny;j++;n[j]=nz;
}

function copyn(v,_n,i,j)
{
	i*=3;j*=3;
	v[i]=_n[j];
	v[i+1]=_n[j+1];
	v[i+2]=_n[j+2];
};

function copyn_i(v,_n,i,j)
{
	i*=3;j*=3;
	v[i]=-_n[j];
	v[i+1]=-_n[j+1];
	v[i+2]=-_n[j+2];
};
mesh3d.prototype.onMeshReady = function(space,buffer) 
{
	var gl=space.gl;
	var data= new DataView(buffer);
	var numPoints=data.getUint16(0,true);
	var numFaces=data.getUint16(2,true);
	var flags=data.getUint16(4,true);
	var offset=6;
	var n3=numPoints*3;
	var nF=numFaces;
	var vminx,vminy,vminz,vmdx,vmdy,vmdz;
	if(flags&8){this.lineMode=true;nF*=2;}else nF*=3;

	// vertices
	v=new Float32Array(n3);
	var voffset=offset;
	offset+=24;
	var index=0,i;
	
	offset+=numPoints*6;
	// faces
	var f=new Uint16Array(nF);
	if(flags&256)
	{
		var bs=new ivBitStream(data,offset);
		ivdcmpf(bs,f,nF);
		offset=bs.stpos;
	}else{
	if(flags&4)
	{
		for(i=0;i<nF;i++)f[i]=data.getUint8(offset++);
	}else
	{
		for(i=0;i<nF;i++)
		{
			f[i]=data.getUint16(offset,true);offset+=2; 
		}}
	}
	this.facesBuffer =ivBufferI(gl,f);

	//normals
	if(flags&16)
	{
		var cs=data.getUint16(offset,true);offset+=2;
		var n;
		if(this.bump)n=new Float32Array(n3);else n=v;
		if(cs)
		{
			var _n=new Float32Array(cs*3);
			for(i=0;i<cs;i++)
			{
				dcdnrml(data,offset,_n,i*3);
				offset+=4;
			}
		var bs=new ivBitStream(data,offset);
		i=0;var j=0,bits=0;
		while(i<numPoints)
		{
			var cd=bs.Read(1);
			if(cd)
			{
				cd=bs.Read(1);
				if(ibits)index=bs.Read(ibits);else index=0;
				if(cd)copyn(n,_n,i,index);else copyn_i(n,_n,i,index);
			}else
			{
				ibits=NumBits(j);
				copyn(n,_n,i,j);j++;
			}
			i++;
		}
		offset=bs.stpos;

		}else{
		for(i=0;i<numPoints;i++)
		{
			dcdnrml(data,offset,n,i*3);
			offset+=4;
		}}
		this.normalBuffer = ivBufferF(gl,n,3);
	}
	
	if(flags&32)// UV
	{
		var uv=new Float32Array(numPoints*2);
		vminx=data.getFloat32(offset,true);
		vminy=data.getFloat32(offset+4,true);
		vmdx=data.getFloat32(offset+8,true);
		vmdy=data.getFloat32(offset+12,true);
		offset+=16;
		index=0;
		for(i=0;i<numPoints;i++)
		{
			uv[index]=vmdx*data.getUint16(offset,true)+vminx;offset+=2;
			uv[index+1]=vmdy*data.getUint16(offset,true)+vminy;offset+=2;
			index+=2;
		}
		this.uvBuffer  = ivBufferF(gl,uv,2);
	}
	
	
	if(flags&64)// per vertex diffuse colors
	{
		var colors = new Uint8Array(n3);
		for(i=0;i<n3;i++)colors[i]=data.getUint8(offset++);
		this.colorBuffer=ivBufferF(gl,colors,3);
	}
	
	offset=voffset;
	vminx=data.getFloat32(offset,true);
	vminy=data.getFloat32(offset+4,true);
	vminz=data.getFloat32(offset+8,true);
	vmdx=data.getFloat32(offset+12,true);
	vmdy=data.getFloat32(offset+16,true);
	vmdz=data.getFloat32(offset+20,true);
	offset+=24;
	this.boxMin=[vminx,vminy,vminz];
	this.boxMax=[vmdx*65535+vminx,vmdy*65535+vminy,vmdz*65535+vminz];
	index=0;
	for(i=0;i<numPoints;i++)
	{
		v[index]=vmdx*data.getUint16(offset,true)+vminx;offset+=2;
		v[index+1]=vmdy*data.getUint16(offset,true)+vminy;offset+=2;
		v[index+2]=vmdz*data.getUint16(offset,true)+vminz;offset+=2;
		index+=3;
	}
	this.vertexBuffer=ivBufferF(gl,v,3);
	if(this.bump){this.updateBumpInfo(gl,f,v,n,uv);delete this.bump;}
	if(space.m_cfgKeepMeshData&1)this.faces=f;
	if(space.m_cfgKeepMeshData&2)this.points=v;
}
;/*EXTENDED API*/


node3d.prototype.getNodeById = function(id) 
{
	if(this.name==id)return this;
	var n=this.firstChild;
	while(n){
		var _n=n.getNodeById(id);
		if(_n)return _n;
		n=n.next;}
}

ivwindow3d.prototype.getNodeById = function(id)
{
	if(this.space && this.space.root)
		return this.space.root.getNodeById(id);
	return null;
}

ivwindow3d.prototype.showNodeById = function(id,bShow,bUpdate)
{
	var n=this.getNodeById(id);
	if(n)
	{
		if(n){
			var old=3;
			if(n.state!=undefined)old=n.state&3;
			if(bShow)n.state|=3;else n.state&=~3;
			if((bUpdate!=undefined) && bUpdate && ((old!=n.state&3)))this.invalidate();
		}
	}
}

ivwindow3d.prototype.setView =function(index)
{
	if(this.space && this.space.views && index>=0 && index<this.space.views.length)
	{
		var v=this.space.views[index];
		if(v)this.setViewImp(v);
	}
}
node3d.prototype.enableTM = function()
{
	if(!this.tm)this.tm=mat4.identity(mat4.create());
}

