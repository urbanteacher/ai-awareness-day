// Hub hero flow (AIADFlow engine), superseded 22 Nov 2026 by the animated flowchart in ai-under-the-hood/index.html
if(window.AIADFlow){var f=document.querySelector('[data-flow=series]');AIADFlow.mount(f,{title:'The whole series in one flow',w:340,h:178,start:['q'],
 nodes:[['q','your question',58,30,'h'],['chip','chip and\nmodel',182,30],['act','tools and\nagents',182,112],['team','teams of\nagents',298,112],['you','you, in\nthe loop',58,112,'ok']],
 edges:[['q','chip',{l:'Part 1'}],['chip','act',{l:'Part 2'}],['act','team',{l:'Part 3'}],['team','you',{c:-42,d:1,l:'checked by'}]],
 steps:[{go:['q>chip'],say:'Part 1: your question travels into a model running on a chip made from sand.'},
  {go:['chip>act'],say:'Part 2: the model gets documents to read, tools to use, and an agent loop that lets it act.'},
  {go:['act>team'],say:'Part 3: agents are combined into workflows and teams to take on bigger jobs.'},
  {go:['team>you'],say:'And at every stage, a person checks what matters.'}],
 end:'Keep Humans in the Loop.'})}
