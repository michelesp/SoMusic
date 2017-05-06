var simulation;

function load(graph) {
	console.log(graph);
	var svg = d3.select("svg"),
	width = +svg.attr("width"),
	height = +svg.attr("height");
	
	simulation = d3.forceSimulation()
			.force("link", d3.forceLink().id(function(d) { return d.id; }))
			.force("charge", d3.forceManyBody())
			.force("center", d3.forceCenter(width / 2, height / 2));

	var link = svg.append("g")
			.attr("class", "links")
			.selectAll("line")
			.data(graph.links)
			.enter().append("line")
			.attr("stroke-width", function(d) { return Math.sqrt(d.value); });

	var gnodes = svg.selectAll('g.gnode')
			.data(graph.nodes)
			.enter()
			.append('g')
			.classed('gnode', true);
	
	var node = svg.append("g")
			.attr("class", "nodes")
			.selectAll("circle")
			.data(graph.nodes)
			.enter().append("circle")
			.attr("r", 10)
			.attr("fill", function(d) {
				if(typeof d.root !== "undefined")
					return '#FF0000';
				return '#2196F3';
			})
			.on("click", click)
			.call(d3.drag()
						.on("start", dragstarted)
						.on("drag", dragged)
						.on("end", dragended));

	node.append("title")
			.text(function(d) { return d.id; });

	var labels = gnodes.append("text")
	  		.text(function(d) { return d.id; });
	
	simulation.nodes(graph.nodes).on("tick", ticked);

	simulation.force("link").links(graph.links);

	function ticked() {
		link.attr("x1", function(d) { return d.source.x; })
			.attr("y1", function(d) { return d.source.y; })
			.attr("x2", function(d) { return d.target.x; })
			.attr("y2", function(d) { return d.target.y; });

		node.attr("cx", function(d) { return d.x; })
			.attr("cy", function(d) { return d.y; });

		gnodes.attr("transform", function(d) { 
			return 'translate(' + [d.x+10, d.y+10] + ')'; 
		});    
	}
}

function click(e){
	location.href = e.url;
    console.log(e);
}

function dragstarted(d) {
	if (!d3.event.active)
		simulation.alphaTarget(0.3).restart();
	d.fx = d.x;
	d.fy = d.y;
}

function dragged(d) {
	d.fx = d3.event.x;
	d.fy = d3.event.y;
}

function dragended(d) {
	if (!d3.event.active)
		simulation.alphaTarget(0);
	d.fx = null;
	d.fy = null;
}