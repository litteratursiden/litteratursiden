import "../sass/style.scss";

import { library, dom } from "@fortawesome/fontawesome-svg-core";

// Import Light icons
import {
  faPrint,
} from "@fortawesome/pro-light-svg-icons";

// Import Brand icons
import {} from "@fortawesome/free-brands-svg-icons";

// Add light and brand icons
library.add(
  faPrint,
);
dom.watch();
