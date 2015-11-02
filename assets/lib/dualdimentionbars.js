function DualDimentionBars( data, options ) { 

  this.container = d3.select(options.container);

  this.setOptions(options);
  
  this.init();

  this.setSizes();

  this.loadData(data);
}

DualDimentionBars.prototype.loadData = function(data) {
  
  this.data = data;
  this.render();
  
}

DualDimentionBars.prototype.setOptions = function(options) {

  this.options = {
    container: options.container,
    width: options.width,
    height: options.height || 400,
    padding: options.padding || 30,
    barHeight: options.barHeight || null,
    yAxisWidth: options.yAxisWidth || 80,
    labelsWidth: options.labelsWidth || 80,
    rowWidth: options.rowWidth || 40,
    labelsTitle: options.labelsTitle,
    yAxisTitle: options.yAxisTitle,
    xAxisTitle: options.xAxisTitle,
    internalMargin: options.internalMargin || 15,
    onClick: options.onClick
  }
  this.options.innerWidth = this.options.width - ( this.options.padding * 2 );
  this.options.innerHeight = this.options.height - ( this.options.padding * 2 );

}

DualDimentionBars.prototype.init = function() {

  var options = this.options;

  this.selectedItems = [];

  this.svg = this.container.append('svg');
  
  this.svgContent = this.svg.append('g')
                      .attr("transform", "translate(" + [ options.padding , options.padding ] + ")");

  // SET UP SCALES
  this.x = d3.scale.linear();
  this.y = d3.scale.ordinal();

  //LABELS COL WRAPPER
  this.labels = this.svgContent.append("g")
              .attr("class", "labels")
              .attr("transform", "translate(" + [ 0 , 0 ] + ")");

  this.labels.append('text')
              .classed('title', true)
              .attr("text-anchor", "middle")
              .attr("dy", '.32em')
              .attr("y", 0)
              .attr("x", options.labelsWidth / 2 )
              .text(function(d){return options.labelsTitle});

  this.columnLine = this.svg.append('line')
            .classed('column-line', true);

  //SET UP AXIS
  this.yAxis = d3.svg.axis()
              .scale(this.y)
              .orient("left");

  this.xAxis = d3.svg.axis()
              .scale(this.x)
              .orient("bottom")
              .tickFormat(function(e) {
                return (Math.floor(e) === e ? e : null);
              });

  this.yAxisEl = this.svgContent.append("g")
              .attr("class", "y axis")
              .attr("transform", "translate(" + [ options.yAxisWidth + options.labelsWidth  , 16 ] + ")")
              .call(this.yAxis);

  this.xAxisEl = this.svgContent.append("g")
              .attr("transform", "translate(" + [ options.yAxisWidth + options.labelsWidth , options.innerHeight + 16 - (options.rowWidth) ] + ")")
              .attr("class", "x axis")
              .call(this.xAxis);

  this.xAxisEl.append('g')
                .classed('title', true)
                .attr('transform', 'translate(' + [ options.innerWidth - options.internalMargin - options.yAxisWidth - options.labelsWidth - 25 , 0 ] + ')')
                  .append('text')
                      .classed('title', true)
                      .attr("dy", '.71em')
                      .attr("y", 9)
                      .attr("x", options.internalMargin)
                      .text(function(d){return options.xAxisTitle});

  this.yAxisEl.append('g')
                .classed('title', true)
                .attr('transform', 'translate(' + [ 0 , -16 ] + ')')
                  .append('text')
                      .classed('title', true)
                      .attr("text-anchor", "end")
                      .attr("dy", '.32em')
                      .attr("y", 0)
                      .attr("x", -9)
                      .text(function(d){return options.yAxisTitle});


  //BARS CHART WRAPPER
  this.bars = this.svgContent.append("g")
              .attr("class", "bars")
              .attr("transform", "translate(" + [ options.yAxisWidth + options.labelsWidth , 16 ] + ")");

  this.options = options;
}

DualDimentionBars.prototype.resize = function(newOptions) { 
  var _this = this;

  Object.keys(newOptions).forEach(function(key) {
    _this.options[key] = newOptions[key];
  })

  this.options.innerWidth = this.options.width - ( this.options.padding * 2 );
  this.options.innerHeight = this.options.height - ( this.options.padding * 2 );

  this.setSizes();
  this.render();

}

DualDimentionBars.prototype.setSizes = function() {

  var options = this.options;  

  this.svg.attr("width", options.width)
          .attr("height", options.height);    
console.log('x range', [0,options.innerWidth - options.internalMargin - options.yAxisWidth - options.labelsWidth - 25]);
  this.x.range([0,options.innerWidth - options.internalMargin - options.yAxisWidth - options.labelsWidth - 25]);
  this.y.rangeBands([options.innerHeight - (options.rowWidth), 0]);

  this.columnLine = this.svg
            .attr('x1', options.labelsWidth + options.padding)
            .attr('x2', options.labelsWidth + options.padding)
            .attr('y1', options.padding - 10)
            .attr('y2', options.innerHeight + options.padding + 10);

}

DualDimentionBars.prototype.render = function() {
  var _this = this;

  //UPDATE THE SCALES DOMAIN
  this.x.domain( [ 0, d3.max(this.data, function(d) { return d.x; }) ] );
  this.y.domain( this.data.map( function(d){ return d.y; } ) );

  this.yAxisEl.call(this.yAxis);
  this.xAxisEl.call(this.xAxis);

  this.bars.classed('with-selection', (_this.selectedItems.length !== 0) );

  //UPDATE THE CHART
  this.barSelection = this.bars.selectAll('rect.bar')
              .data(this.data);

  this.barSelection.enter()
              .append('rect')
                .classed('bar', true)
                .attr("y", function(d) { return 0; })
                .attr("x", function(d) { return 0; })
                .attr("height", function(d) { return _this.options.barHeight ? _this.options.barHeight : _this.y.rangeBand(); })
                .attr("width", function(d) { return 0; })
                .on('click', function(d) {
                  _this.data.forEach(function(e){
                    e.selected = (e.selected || false);
                    e.selected = (e.id === d.id) ? !e.selected : e.selected;
                    return e;
                  });

                  _this.selectedItems = _this.data.filter( function(e) { return e.selected; } );

                  if (_this.options.onClick) {
                    _this.options.onClick( _this.selectedItems );
                  }
                  _this.render();
                })
                .on('mouseover', function(d){
                  _this.data.forEach(function(e){
                    e.hover = (e.id === d.id) ? true : false;
                    return e;
                  });
                  _this.render();
                })
                .on('mouseout', function(d){
                  _this.data.forEach(function(e){
                    e.hover = false;
                    return e;
                  });
                  _this.render();
                });

  this.barSelection.exit().remove();


  var barPositionDelta = this.options.barHeight ? ( _this.y.rangeBand() - this.options.barHeight ) / 2 : 0;

  this.barSelection
              .classed('selected', function(d) { return d.selected; })
              .classed('hover', function(d) { return d.hover; })
              .transition()
                .attr("y", function(d) { return _this.y(d.y) + barPositionDelta; })
                .attr("x", function(d) { return 0; })
                .attr("height", function(d) { return _this.options.barHeight ? _this.options.barHeight : _this.y.rangeBand(); })
                .attr("width", function(d) { return _this.x(d.x); });

  this.yAxisEl.selectAll('.tick')
              .classed('selected', function(d, i) { return _this.data[i].selected; })
              .classed('hover', function(d, i) { return _this.data[i].hover; });

  //UDPATE THE LABELS
  this.labelSelection = this.labels.selectAll('text.label')
              .data(this.data);

  this.labelSelection.enter()
              .append('text')
                .classed('label', true);

  this.labelSelection.exit().remove();

  this.labelSelection
              .classed('selected', function(d) { return d.selected; })
              .classed('hover', function(d) { return d.hover; })
              .attr("text-anchor", "middle")
              .attr("dy", '.32em')
              .attr("y", function(d) { return _this.y(d.y) + barPositionDelta + 16; })
              .attr("x", this.options.labelsWidth / 2 )
              .text(function(d){return d.label});
}

DualDimentionBars.prototype.debug = function() { 

  var options = this.options; 

  this.svg.append('line')
            .attr('x1', options.width / 2)
            .attr('x2', options.width / 2)
            .attr('y1', options.padding)
            .attr('y2', options.innerHeight + options.padding)
            .style('stroke','rgb(255,128,128)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', options.padding)
            .attr('x2', options.innerWidth + options.padding)
            .attr('y1', options.height / 2)
            .attr('y2', options.height / 2)
            .style('stroke','rgb(255,128,128)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', options.padding)
            .attr('x2', options.padding)
            .attr('y1', 0)
            .attr('y2', options.height)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', options.labelsWidth + options.padding)
            .attr('x2', options.labelsWidth + options.padding)
            .attr('y1', 0)
            .attr('y2', options.height)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1);


  this.svg.append('line')
            .attr('x1', options.labelsWidth + options.yAxisWidth + options.padding)
            .attr('x2', options.labelsWidth + options.yAxisWidth + options.padding)
            .attr('y1', 0)
            .attr('y2', options.height)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', options.width - options.padding)
            .attr('x2', options.width - options.padding)
            .attr('y1', 0)
            .attr('y2', options.height)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', options.width - 25 - options.padding - options.internalMargin)
            .attr('x2', options.width - 25 - options.padding - options.internalMargin)
            .attr('y1', 0)
            .attr('y2', options.height)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', 0)
            .attr('x2', options.width)
            .attr('y1', options.padding)
            .attr('y2', options.padding)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', 0)
            .attr('x2', options.width)
            .attr('y1', options.height - options.padding)
            .attr('y2', options.height - options.padding)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1);

  this.svg.append('line')
            .attr('x1', 0)
            .attr('x2', options.width)
            .attr('y1', options.height - options.rowWidth - options.padding)
            .attr('y2', options.height - options.rowWidth - options.padding)
            .style('stroke','rgb(255,0,0)')
            .style('stroke-width',1); 
}