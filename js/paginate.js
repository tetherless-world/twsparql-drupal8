function loadPage(year, page) {
  if(year=="All") year = "";
  if(window.lastYear != year) {
    fetch(`${window.twsparql}?uri=${window.instanceURI}&query=person-publications-paginate-footer.rq&xslt=person-publications-paginate-footer.xsl&year=${year}`)
      .then((response) => {
        return response.text()
      })
      .then((text) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, "application/xml");
        window.data = doc;
        const elem = doc.querySelector(".person-publications-paginate-footer");
        window.elem = elem;
        const node = document.importNode(elem,true);
        window.node = node;
        const toReplace = document.querySelector(".person-publications-paginate-footer");
        toReplace.parentNode.replaceChild(node, toReplace);
        window.activePage = node.querySelector("span.selected");
        node.querySelectorAll("span.page").forEach((element) => {
          element.onclick = (e) => {
            const pageOffset = e.target.innerText || e.target.textContent;
            const yearNumber = window.activeYear.innerText || window.activeYear.textContent;
            loadPage(yearNumber, window.pageOffset=(parseInt(pageOffset)-1));
            window.activePage.classList.remove("selected");
            window.activePage = e.target;
            window.activePage.classList.add("selected");
          };
        });
      })
      .catch((e) => {
        console.log("error", e);
      });
  }
  const offset = page*10;
  const endpoint = `${window.twsparql}?uri=${window.instanceURI}&query=person-publications-paginate.rq&xslt=generate/person-publications-paginate.xsl&year=${year}&limig=${window.pageLimit}&offset=${offset}`
  fetch(endpoint)
    .then((response) => {
      return response.text()
    })
    .then((text) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(text, "application/xml");
      window.data = doc;
      const elem = doc.querySelector(".papers-list");
      window.elem = elem;
      const node = document.importNode(elem,true);
      window.node = node;
      const toReplace = document.querySelector(".papers-list");
      toReplace.parentNode.replaceChild(node, toReplace);
    })
    .catch((e) => {
      console.log("error", e);
    });
}

function fixIEpaginate() {
    if (document.ELEMENT_NODE == null) {
	document.ELEMENT_NODE = 1;
	document.ATTRIBUTE_NODE = 2;
	document.TEXT_NODE = 3;
	document.CDATA_SECTION_NODE = 4;
	document.ENTITY_REFERENCE_NODE = 5;
	document.ENTITY_NODE = 6;
	document.PROCESSING_INSTRUCTION_NODE = 7;
	document.COMMENT_NODE = 8;
	document.DOCUMENT_NODE = 9;
	document.DOCUMENT_TYPE_NODE = 10;
	document.DOCUMENT_FRAGMENT_NODE = 11;
	document.NOTATION_NODE = 12;
    }
    if (!document.importNode) {
	document.importNode = function(node, allChildren) {
	    switch (node.nodeType) {
	    case document.ELEMENT_NODE:
		var newNode = document.createElement(node.nodeName);
		/* does the node have any attributes to add? */
		if (node.attributes && node.attributes.length > 0) {
		    for (var i = 0, il = node.attributes.length; i < il; i++) {
			newNode.setAttribute(node.attributes[i].nodeName,
					     node.getAttribute(node.attributes[i].nodeName));
		    }
		}
		/* are we going after children too, and does
		   the node have any? */
		if (allChildren && node.childNodes && node.childNodes.length > 0)
		    for (var i = 0, il = node.childNodes.length; i < il; i++)
			newNode.appendChild(document.importNode(node.childNodes[i], allChildren));
		return newNode;
	    case document.TEXT_NODE:
	    case document.CDATA_SECTION_NODE:
	    case document.COMMENT_NODE:
		return document.createTextNode(node.nodeValue);
	    }
	};
    }
}

function paginate() {
    window.paginated = true;
    fixIEpaginate();
    window.pageOffset = 0;
    window.lastYear = "";
    window.pageHeader = document.querySelector(".person-publications-paginate-header");
    window.pageFooter = document.querySelector(".person-publications-paginate-footer");
    window.activeYear = window.pageHeader.querySelector("span.selected");
    window.activePage = window.pageFooter.querySelector("span.selected");
    window.pageHeader.querySelectorAll("span.yearOption").forEach((element) => {
      element.onclick = (e) => {
        const yearNumber = e.target.innerText || e.target.textContent;
        loadPage(yearNumber,window.pageOffset=0);
        if(yearNumber == "All") window.lastYear = "";
        else window.lastYear = yearNumber;
        window.activeYear.classList.remove("selected");
        window.activeYear = e.target;
        window.activeYear.classList.add("selected");
      }
    });
    window.pageFooter.querySelectorAll("span.page").forEach((element) => {
      element.onclick = (e) => {
        const pageOffset = e.target.innerText || e.target.textContent;
        const yearNumber = window.activeYear.innerText || window.activeYear.textContent;
        loadPage(yearNumber, window.pageOffset=(parseInt(pageOffset)-1));
        window.activePage.classList.remove("selected");
        window.activePage = e.target;
        window.activePage.classList.add("selected");
      };
    });
}
