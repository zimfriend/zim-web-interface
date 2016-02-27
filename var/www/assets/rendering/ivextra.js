


node3d.prototype.getBoundingBox=function(tm,b)
{
	if(!(this.state&3))return b;
	
	if(this.object && this.object.points)
	{
		var v=this.object.points;
		if(!b)b=[];
		var p=[];
		var c=v.length;
		for(var i=0;i<c;i+=3)
		{
			p[0]=v[i];p[1]=v[i+1];p[2]=v[i+2];
			if(tm)mat4.mulPoint(tm,p);
			if(b.length)
			{
				for(var j=0;j<3;j++)
				{
					if(p[j]<b[j])b[j]=p[j];else
					if(p[j]>b[j+3])b[j+3]=p[j];
				}
			}else{b[3]=b[0]=p[0];b[4]=b[1]=p[1];b[5]=b[2]=p[2];}
			
		}
	}

	var child=this.firstChild;
	while(child)
	{	
		var newtm;
		if(child.tm)
		{
			if(tm){
				newtm = mat4.create();
				mat4.m(child.tm,tm,newtm);
			}else newtm=child.tm;
		}else newtm=tm;
		b=child.getBoundingBox(newtm,b);
		child=child.next;
	}
	return b;
}
