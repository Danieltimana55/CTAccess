<script setup>
import { computed } from 'vue'
import {
  // Navigation & Layout
  Home,
  Users,
  Key,
  QrCode,
  AlertTriangle,
  FileText,
  Settings,
  Shield,
  Sun,
  Moon,
  
  // Actions
  ArrowLeft,
  ArrowRight,
  Check,
  X,
  Plus,
  Minus,
  Edit,
  Trash2,
  Save,
  Search,
  Filter,
  RefreshCw,
  Download,
  Upload,
  
  // Status & Feedback
  CheckCircle,
  XCircle,
  AlertCircle,
  Info,
  Loader2,
  
  // Devices & Objects
  Laptop,
  Car,
  Smartphone,
  Camera,
  Briefcase,
  Inbox,
  Badge,
  
  // People & Access
  User,
  UserCheck,
  UserX,
  UserPlus,
  LogIn,
  LogOut,
  
  // Time & History
  Clock,
  Calendar,
  History,
  
  // Communication
  Mail,
  Phone,
  MessageSquare,
  
  // Data & Files
  Database,
  File,
  Folder,
  BookOpen,
  GraduationCap,
  
  // UI Elements
  ChevronDown,
  ChevronUp,
  ChevronLeft,
  ChevronRight,
  Menu,
  MoreVertical,
  Eye,
  EyeOff,
  
  // Misc
  Building,
  MapPin,
  Tag,
  Star,
  Heart,
  Zap,
  
  // Weather
  Droplet,
  Wind,
  Activity,
  BarChart2
} from 'lucide-vue-next'

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  size: {
    type: [String, Number],
    default: 20
  },
  color: {
    type: String,
    default: 'currentColor'
  },
  strokeWidth: {
    type: [String, Number],
    default: 2
  },
  class: {
    type: String,
    default: ''
  }
})

// Mapeo de nombres a componentes
const iconMap = {
  // Navigation & Layout
  'home': Home,
  'dashboard': Home,
  'users': Users,
  'personas': Users,
  'key': Key,
  'accesos': Key,
  'qr-code': QrCode,
  'qr': QrCode,
  'alert-triangle': AlertTriangle,
  'incidencias': AlertTriangle,
  'file-text': FileText,
  'historial': FileText,
  'settings': Settings,
  'shield': Shield,
  
  // Actions
  'arrow-left': ArrowLeft,
  'arrow-right': ArrowRight,
  'check': Check,
  'x': X,
  'plus': Plus,
  'minus': Minus,
  'edit': Edit,
  'trash': Trash2,
  'save': Save,
  'search': Search,
  'filter': Filter,
  'refresh': RefreshCw,
  'download': Download,
  'upload': Upload,
  
  // Status & Feedback
  'check-circle': CheckCircle,
  'success': CheckCircle,
  'x-circle': XCircle,
  'error': XCircle,
  'alert-circle': AlertCircle,
  'warning': AlertCircle,
  'info': Info,
  'loader': Loader2,
  'loading': Loader2,
  
  // Devices & Objects
  'laptop': Laptop,
  'portatil': Laptop,
  'car': Car,
  'vehiculo': Car,
  'smartphone': Smartphone,
  'phone': Smartphone,
  'camera': Camera,
  'briefcase': Briefcase,
  'maletin': Briefcase,
  'inbox': Inbox,
  'bandeja': Inbox,
  'badge': Badge,
  'documento': Badge,
  
  // People & Access
  'user': User,
  'persona': User,
  'user-check': UserCheck,
  'user-x': UserX,
  'user-plus': UserPlus,
  'log-in': LogIn,
  'entrada': LogIn,
  'log-out': LogOut,
  'salida': LogOut,
  
  // Time & History
  'clock': Clock,
  'tiempo': Clock,
  'calendar': Calendar,
  'fecha': Calendar,
  'history': History,
  
  // Communication
  'mail': Mail,
  'correo': Mail,
  'phone-call': Phone,
  'telefono': Phone,
  'message': MessageSquare,
  
  // Data & Files
  'database': Database,
  'file': File,
  'folder': Folder,
  'book-open': BookOpen,
  'programa': BookOpen,
  'graduation-cap': GraduationCap,
  'formacion': GraduationCap,
  
  // UI Elements
  'chevron-down': ChevronDown,
  'chevron-up': ChevronUp,
  'chevron-left': ChevronLeft,
  'chevron-right': ChevronRight,
  'menu': Menu,
  'more': MoreVertical,
  'eye': Eye,
  'eye-off': EyeOff,
  
  // Misc
  'building': Building,
  'empresa': Building,
  'map-pin': MapPin,
  'ubicacion': MapPin,
  'tag': Tag,
  'etiqueta': Tag,
  'star': Star,
  'favorito': Star,
  'heart': Heart,
  'zap': Zap,
  
  // Weather
  'droplet': Droplet,
  'humidity': Droplet,
  'wind': Wind,
  'activity': Activity,
  'bar-chart': BarChart2,
  'energia': Zap,
  'sun': Sun,
  'sol': Sun,
  'moon': Moon,
  'luna': Moon
}

const IconComponent = computed(() => {
  const component = iconMap[props.name.toLowerCase()]
  if (!component) {
    console.warn(`Icon "${props.name}" not found. Available icons:`, Object.keys(iconMap))
    return Info // Fallback icon
  }
  return component
})

const iconProps = computed(() => ({
  size: props.size,
  color: props.color,
  strokeWidth: props.strokeWidth,
  class: props.class
}))
</script>

<template>
  <component :is="IconComponent" v-bind="iconProps" />
</template>
