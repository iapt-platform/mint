import { Grid, h } from "../../node_modules/gridjs/dist/gridjs.module.js";
export var _rowSelected =   new Array();
const grid = new gridjs.Grid({
	sort: {
		multiColumn: false,
		server: {
		  url: (prev, columns) => {
		   if (!columns.length) return prev;
		   
		   const col = columns[0];
		   const dir = col.direction === 1 ? 'asc' : 'desc';
		   let colName = ['id', 'word','type','grammar','mean','factors','confidence','updated_at'][col.index];
		   
		   return `${prev}&order=${colName}&dir=${dir}`;
		 }
		}
	  },
	columns: [
		{ 
			name: 'Sel',
			hidden: false,
			sort: false,
			formatter: (cell, row) => {
			return h('input', {
				type:'checkbox',
				className: 'py-2 mb-4 px-4 border',
				id:"cb-"+row.cells[1].data,
				onClick: () =>{
					let id = row.cells[1].data;
					if(document.querySelector("#cb-"+id).checked){
						_rowSelected.push(id);
						console.log("checked",_rowSelected);
					}else{
						_rowSelected.splice(_rowSelected.findIndex(item => item ===id),1);
						console.log("remove",_rowSelected);
					}
				} 
			}, '');
			}
      	},
		{
			name: 'id',
			hidden: true
		},
		'word',
		{
			name:'type',
			sort: false,
		},
		{
			name:'grammar',
			sort: false,
		},
		{
			name:'mean',
			sort: false,
		},
		{
			name:'factors',
			sort: false,
		},
		'confidence','created_at',
		{ 
			name: 'Actions',
			sort: false,
			hidden:true,
			formatter: (cell, row) => {
			return h('button', {
				className: 'py-2 mb-4 px-4 border rounded-md text-white bg-blue-600',
				onClick: () =>{
					
					
				} 
			}, 'Edit');
			}
      	}
	],
	server: {
		url: '/api/v2/userdict?view=user',
		then: data => data.data.rows.map(card => [null,card.id,card.word, card.type, card.grammar, card.mean, card.factors, card.confidence, card.updated_at,null]),
		total: data => data.data.count
	  },
	pagination: {
		enabled: true,
		limit:30,
		server: {
			url: (prev, page, limit) => `${prev}&limit=${limit}&offset=${page * limit}`
		  }
	},
	search: {
		server: {
		  url: (prev, keyword) => `${prev}&search=${keyword}`
		}
	  },
	resizable: true,
  }).render(document.getElementById("userfilelist"));

//grid.on('rowClick', (...args) => console.log('row: ' + JSON.stringify(args), args));
//grid.on('cellClick', (...args) => console.log(cell,args));

document.querySelector("#to_recycle").onclick = function(){
	if(_rowSelected.length>0){
		if(confirm(`删除${_rowSelected.length}个单词，此操作不能恢复。`)){
			let deleteId = JSON.stringify({
				id:JSON.stringify(_rowSelected)
			});
			console.log("delete id:",deleteId);
		fetch('/api/v2/userdict',{
				method: 'DELETE',
				credentials: 'include',
				headers: {
					'Content-Type': 'application/json'
				},
				body: deleteId
			})
		  .then(response => response.json())
		  .then(function(data){
			  console.log(data);
			  if(data.ok){
				_rowSelected = new Array();
				grid.forceRender();
				alert(`delete ok `+data.data.deleted);
			}
		  });
		
		}
	}
}